<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;
use \Nette\Utils\Json;
use POS\Model\ChatMessagesDao;

/**
 * Slouží přímo ke komunikaci mezi uživateli
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardCommunicator extends BaseProjectControl implements ICommunicator {

	/**
	 * Jmeno session, kam se ukladaji jmena uzivatelu
	 */
	const USERNAMES_SESSION_NAME = 'chat_usernames';

	/**
	 * Prefix session, kde se registruji pozadavky na zpravu o doruceni zpravy
	 */
	const DELIVERY_SESSION_NAME = 'chat_delivery_requests';

	/**
	 * co se pošle uživateli za text v případě, že byla jeho zpráva doručena
	 */
	const DELIVERY_MESSAGE = 'Doručeno.';

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager) {
		$this->chatManager = $manager;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$template->sendMessageLink = $this->link("sendMessage!");
		$template->refreshMessagesLink = $this->link("refreshMessages!");
		$template->render();
	}

	/**
	 * Zpracuje poslani zpravy zaslane prohlizecem ve formatu JSON
	 */
	public function handleSendMessage() {
		$json = file_get_contents("php://input");
		$data = Json::decode($json); //prijata zprava
		if ($data && !empty($data) && $data->type == 'textMessage') {//ulozeni zpravy do DB
			$sender = $this->getPresenter()->getUser()->getId();
			$data->to = (int) $this->chatManager->getCoder()->decodeData($data->to); //dekodovani id
			$message = $this->chatManager->sendTextMessage($sender, $data->to, $data->text); //ulozeni zpravy
			if ($this->isActualUserPaying()) {//pokud je uzivatel platici
				$this->registerInfoAboutDelivery($data->to, $message->offsetGet(ChatMessagesDao::COLUMN_ID));
			}
			$this->sendRefreshResponse();
		}
	}

	/**
	 * Zjistí, jestli je aktuální uživatel platící
	 * @return bool platící/neplatící
	 */
	private function isActualUserPaying() {
		$userId = $this->getPresenter()->getUser()->getId();
		return $this->chatManager->isUserPaying($userId);
	}

	/**
	 * Vyřízení žádosti o poslání nových zpráv
	 */
	public function handleRefreshMessages() {
		$this->sendRefreshResponse();
	}

	/**
	 * Pošle uživateli JSON, obsahující informace o nových zprávách apod.
	 * Vrací odpověď prohlížeči, vykonání kódu na serveru zde končí.
	 */
	public function sendRefreshResponse() {
		$user = $this->getPresenter()->getUser()->getId();
		$newMessages = $this->chatManager->getAllNewMessages($user);
		$response = $this->prepareResponseArray($newMessages);
		if ($this->isActualUserPaying()) {
			$response = $this->addInfoAboutDeliveredMessages($response);
		}
		$this->chatManager->readMessages($newMessages);

		$this->getPresenter()->sendJson($response);
	}

	/**
	 * Převede výběr z tabulky na pole, kterému bude možné odeslat zpět prohlížeči
	 * @param Selection $selection vyber z tabulky
	 * @return array pole ve formatu pro JSON
	 */
	private function prepareResponseArray($selection) {
		$array = array();
		foreach ($selection as $row) {
			$sender = $row->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER); //ziskani id odesilatele
			$senderCoded = $this->chatManager->getCoder()->encodeData($sender);
			if (!array_key_exists($senderCoded, $array)) {//pokud je tohle prvni zprava od tohoto uzivatele
				$name = $this->getUsername($sender); //pribaleni uzivatelskeho jmena
				$array[$senderCoded] = array('name' => $name, 'messages' => array()); //pak vytvori pole na zpravy od tohoto uzivatele
			}
			array_push($array[$senderCoded]['messages'], $this->modifyResponseRowToArray($row)); //do pole pod klicem odesilatele v poli $array vlozi pole se zpravou
		}
		return $array;
	}

	private function modifyResponseRowToArray(\Nette\Database\Table\IRow $row) {
		$rowArray = $row->toArray();
		unset($rowArray[ChatMessagesDao::COLUMN_ID]); //id odesilatele je uz v prvnim klici pole
		unset($rowArray[ChatMessagesDao::COLUMN_ID_SENDER]); //id odesilatele je uz v prvnim klici pole
		unset($rowArray[ChatMessagesDao::COLUMN_ID_RECIPIENT]);  //neposila uzivateli jeho vlastni id
		unset($rowArray[ChatMessagesDao::COLUMN_READED]);  //neposila zbytecnou informaci
		return $rowArray;
	}

	/**
	 * Vrátí uživatelské jméno uživatele s daným id
	 * Šetří databázi.
	 * @param int $id id uživatele
	 * @return String Uzivatelske jmeno
	 */
	public function getUsername($id) {
		$session = $this->getPresenter()->getSession(self::USERNAMES_SESSION_NAME);
		$session->setExpiration(0);
		return $this->chatManager->getUsername($id, $session);
	}

	/**
	 * Přidá zprávu odeslanou určitému uživateli do seznamu zpráv, u kterých
	 * chceme sledovat, zda byly doručeny. Přepisuje dříve zaregistrované
	 * zprávy adresované stejnému uživateli. Pozn. ID se zde nesifruji.
	 * @param int $idRecipient id příjemce
	 * @param int $idMessage id sledované zprávy
	 */
	public function registerInfoAboutDelivery($idRecipient, $idMessage) {
		$session = $this->getDeliverySession();
		$session->offsetSet($idRecipient, $idMessage);
	}

	/**
	 * Přidá do pole odpovědi také informační zprávu o tom, že poslední
	 * zpráva odeslaná danému uživateli byla přečtena (pokud byla). Pokud
	 * je již uživatel v poli (tj. posílá zprávu), informační zpráva se nepřidá.
	 * V případě, že daný uživatel poslal zprávu nebo ji přečetl, se smaže požadavek
	 * na zjišťování stavu zprávy, registrovaný pomocí registerInfoAboutDelivery
	 * @param array $array pole odpovědi, kam se má informace přidat
	 * @return array doplnene pole
	 */
	public function addInfoAboutDeliveredMessages($array) {
		$session = $this->getDeliverySession();
		$userId = $this->getPresenter()->getUser()->getId();
		$undeliveredMessages = $this->chatManager->getAllUnreadedMessagesFromUser($userId);
		foreach ($session as $idRecipient => $idMessage) {//vsechny registrovane pozadavky o precteni
			$array = $this->resolveSingleRegisteredInfoRequest($idRecipient, $idMessage, $undeliveredMessages, $session, $array);
		}
		return $array;
	}

	/**
	 * Vrati session s zadostmi o info o doruceni podle aktualniho uzivatele
	 * @return \Nette\Http\SessionSection session uzivatele
	 */
	private function getDeliverySession() {
		$userId = $this->getPresenter()->getUser()->getId();
		return $this->getPresenter()->getSession(self::DELIVERY_SESSION_NAME . $userId);
	}

	/**
	 * Zjistí, zda má smysl předávat uživateli zprávu o doručení a pokud
	 * ano, zavolá příslušnou metodu
	 * @param int $idRecipient skutečné id příjemce
	 * @param int $idMessage id zprávy
	 * @param array $undeliveredMessages seznam nedoručených zpráv
	 * @param \Nette\Http\SessionSection $session session pro seznam žádostí
	 * @param array $array pole na data prohlížeči
	 * @return array doplnene pole
	 */
	private function resolveSingleRegisteredInfoRequest($idRecipient, $idMessage, $undeliveredMessages, $session, $array) {
		$recipientCoded = $this->chatManager->getCoder()->encodeData($idRecipient); //data v poli jsou jiz zakodovana
		if (array_key_exists($recipientCoded, $array)) {//v poli je uzivatel, u nejz cekame na precteni - tj. posila zpravu
			$session->offsetUnset($idRecipient); //nepotrebujeme tedy navic informovat o precteni
		} else {//uzivatel novou zpravu neposlal
			$array = $this->addInfoIfMessageWasReaded($session, $idMessage, $idRecipient, $recipientCoded, $undeliveredMessages, $array);
		}
		return $array;
	}

	/**
	 * Porovná žádost o info o doručení a seznam
	 * nedoručených zpráv. Podle toho pak aktualizuje session seznamu a pole s daty.
	 * @param \Nette\Http\SessionSection $session session pro seznam žádostí
	 * @param int $idMessage id zprávy
	 * @param int $idRecipient skutečné id příjemce
	 * @param int $recipientCoded kódované id příjemce v datech pro prohlížeč
	 * @param array $undeliveredMessages seznam nedoručených zpráv
	 * @param array $array pole na data prohlížeči
	 * @return array doplnene pole
	 */
	private function addInfoIfMessageWasReaded($session, $idMessage, $idRecipient, $recipientCoded, $undeliveredMessages, $array) {
		if (array_key_exists($idMessage, $undeliveredMessages)) {//zprava nebyla prectena
			//nedela nic
		} else {//zprava byla prectena
			$array[$recipientCoded]['messages'] = array($this->createDeliveryInfoMessage());
			$array[$recipientCoded]['name'] = $this->getUsername($idRecipient);
			$session->offsetUnset($idRecipient);
		}
		return $array;
	}

	private function createDeliveryInfoMessage() {
		return array(
			ChatMessagesDao::COLUMN_TEXT => self::DELIVERY_MESSAGE,
			ChatMessagesDao::COLUMN_TYPE => ChatMessagesDao::TYPE_INFO_MESSAGE
		);
	}

}
