<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use \Nette\Utils\Json;
use POS\Model\ChatMessagesDao;
use Nette\Database\Table\IRow;

/**
 * Slouží přímo ke komunikaci mezi serverem a prohlížečem, zpracovává
 * požadavky a vrací odpovědi. Veškerá komunikace ajaxem probíhá zde.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardCommunicator extends BaseChatComponent implements ICommunicator {

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
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$template->sendMessageLink = $this->link("sendMessage!");
		$template->refreshMessagesLink = $this->link("refreshMessages!");
		$template->loadMessagesLink = $this->link("loadMessages!");
		$template->render();
	}

	/**
	 * Zpracuje poslani zpravy zaslane prohlizecem ve formatu JSON
	 */
	public function handleSendMessage() {
		$json = file_get_contents("php://input"); //vytánutí všech dat z POST požadavku - data ve formátu JSON
		$data = Json::decode($json); //prijata zprava dekodovana z JSONu
		$user = $this->getPresenter()->getUser();
		if (!empty($data) && $data->type == 'textMessage' && $user->isLoggedIn()) {//ulozeni zpravy do DB
			$senderId = $user->getId();
			$data->to = (int) $this->chatManager->getCoder()->decodeData($data->to); //dekodovani id
			$message = $this->chatManager->sendTextMessage($senderId, $data->to, $data->text); //ulozeni zpravy
			if ($this->isActualUserPaying()) {//pokud je uzivatel platici
				$this->registerInfoAboutDelivery($data->to, $message->offsetGet(ChatMessagesDao::COLUMN_ID));
			}
			$this->sendRefreshResponse($data->lastid);
		}
	}

	/**
	 * Vyřízení žádosti o poslání nových zpráv (prohlížeč se ptá serveru, zda nejsou nějaké nové zprávy)
	 * @param int $lastid posledni zname id
	 * @param json $readedmessages pole idcek prectenych zprav
	 */
	public function handleRefreshMessages($lastid, $readedmessages) {
		$user = $this->getPresenter()->getUser();
		if ($user->isLoggedIn()) {
			$readedArray = (array) Json::decode($readedmessages);
			$this->chatManager->setOlderMessagesReaded($readedArray, $user->getId(), TRUE); //oznaceni zprav za prectene
			$this->sendRefreshResponse($lastid);
		}
	}

	/**
	 * Vrátí zprávy z konverzace jednoho uživatele s přihlášeným uživatelem
	 * @param int $fromId druhý uživatel
	 */
	public function handleLoadMessages($fromId) {
		$realId = $this->chatManager->getCoder()->decodeData($fromId);
		$userId = $this->getPresenter()->getUser()->getId();
		$messages = $this->chatManager->getLastMessagesBetween($userId, $realId);
		$response = $this->prepareResponseArray($messages);

		$this->registerInfoToLastMessage($realId, $fromId, $response);

		$this->getPresenter()->sendJson($response);
	}

	/**
	 * Vezme pole odpovědi a k danému uživateli, se kterým si píšu, zaregistruje žádost
	 * o potvrzení přijetí poslední zprávy, pokud je tato zpráva odeslána přihlášeným uživatelem
	 * @param int $userId id uživatele, se kterým si píšu
	 * @param int $codedId kódované id uživatele, se kterým si píšu
	 * @param array $response pole odpovědi viz dokumentace
	 */
	private function registerInfoToLastMessage($userId, $codedId, $response) {
		if ($this->isActualUserPaying() && !empty($response)) {//pokud je uzivatel platici
			$lastMessage = end($response[$codedId]['messages']); //posledni zprava z posilanych
			if ($lastMessage['fromMe'] == 1) {//zprava je ode me
				$this->registerInfoAboutDelivery($userId, $lastMessage[ChatMessagesDao::COLUMN_ID]);
			}
		}
	}

	/**
	 * Zjistí, jestli je aktuální uživatel platící
	 * @return bool platící/neplatící
	 */
	private function isActualUserPaying() {
		$userId = $this->getPresenter()->getUser()->getId();
		$session = $this->getPresenter()->getSession(\SignPresenter::USER_INFO_SESSION_NAME);
		$session->setExpiration(0);
		if (!empty($session->isPaying)) { //kdyz je v session
			return $session->isPaying; //vrati hodnotu
		} else {   //kdyz ne
			$paying = $this->chatManager->isUserPaying($userId); //podiva se do db
			$session->isPaying = $paying;   //ulozi do session
			return $paying; //a vrati hodnotu
		}
	}

	/**
	 * Pošle uživateli JSON, obsahující informace o nových zprávách apod.
	 * Vrací odpověď prohlížeči, vykonání kódu na serveru zde končí.
	 * @param int $lastId id poslední známé zprávy
	 */
	private function sendRefreshResponse($lastId = 0) {
		$userId = $this->getPresenter()->getUser()->getId();
		if (!$lastId || $lastId == 0) {//pokud jde o prvni pozadavek prohlizece
			$newMessages = $this->chatManager->getInitialMessages($userId); //vrati nam to nejake zpravy pro zacatek
		} else {
			$newMessages = $this->chatManager->getAllNewMessages($lastId, $userId);
		}
		$response = $this->prepareResponseArray($newMessages);
		if ($this->isActualUserPaying()) {
			$response = $this->addInfoAboutDeliveredMessages($response);
		}
		$this->getPresenter()->sendJson($response);
	}

	/**
	 * Převede výběr z tabulky zpráv na pole, které bude možné odeslat zpět prohlížeči
	 * @param Selection $messages vyber z tabulky chat_messages
	 * @return array pole ve formatu pro JSON ve tvaru dokumentace chatu
	 */
	private function prepareResponseArray($messages) {
		$responseArray = array();
		foreach ($messages as $message) {
			$userId = $this->getRelatedUserId($message);
			$userIdCoded = $this->chatManager->getCoder()->encodeData($userId);
			if (!array_key_exists($userIdCoded, $responseArray)) {//pokud je tohle prvni zprava od tohoto uzivatele
				$name = $this->getUsername($userId); //pribaleni uzivatelskeho jmena uzivatele, s kterym komunikuji
				$href = $this->getPresenter()->link(':Profil:Show:', array('id' => $userId));
				$responseArray[$userIdCoded] = array('name' => $name, 'href' => $href, 'messages' => array()); //pak vytvori pole na zpravy od tohoto uzivatele
			}
			$responseArray[$userIdCoded]['messages'][] = $this->modifyResponseRowToArray($message); //do pole pod klicem odesilatele v poli $responseArray vlozi pole se zpravou
			usort($responseArray[$userIdCoded]['messages'], array($this, 'messageSort')); //seřadí zprávy
		}
		return $responseArray;
	}

	/**
	 * Porovnání dvou zpráv pro usort
	 * @param mixed $a první zpráva
	 * @param mixed $b druhá zpráva
	 */
	private function messageSort($a, $b) {
		if ($a[ChatMessagesDao::COLUMN_ID] == $b[ChatMessagesDao::COLUMN_ID]) {
			return 0;
		}
		return ($a[ChatMessagesDao::COLUMN_ID] < $b[ChatMessagesDao::COLUMN_ID]) ? -1 : 1;
	}

	/**
	 * Vrátí id  uživatele, který vzhledem k přihlášenému
	 * uživateli souvisí s danou zprávou (například pokud si píšu s id 80, vrátí id 80,
	 * ať už jsem psal já jenmu nebo on mně)
	 * @param \Nette\Database\Table\IRow $message zpráva
	 * @return int id souvisejícíhouživatele
	 */
	private function getRelatedUserId($message) {
		$userId = $this->getPresenter()->getUser()->getId();
		$relatedUserId = $message->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER); //ziskani id odesilatele
		if ($relatedUserId == $userId) {//pokud je prihlaseny user odesilatel
			$relatedUserId = $message->offsetGet(ChatMessagesDao::COLUMN_ID_RECIPIENT); //pak je souvisejici uzivatel prijemce
		}
		return $relatedUserId;
	}

	/**
	 * Modifikuje řádek z pole, které se vrací prohlížeči
	 * @param \Nette\Database\Table\IRow $message
	 * @return array pole
	 */
	private function modifyResponseRowToArray(IRow $message) {
		$messageArray = $message->toArray();
		$messageArray['name'] = $this->getUsername($messageArray[ChatMessagesDao::COLUMN_ID_SENDER]); //pribaleni uzivatelskeho jmena uzivatele, ktery poslal zpravu
		//pozn. muze to byt jiny uzivatel nez ten, s kterym si pisu (typicky ja)
		$userId = $this->getPresenter()->getUser()->getId();
		if ($userId == $messageArray[ChatMessagesDao::COLUMN_ID_SENDER]) {//pokud jsem zpravu odeslal ja (prihlaseny uzivatel)
			$messageArray['fromMe'] = 1;
		} else {
			$messageArray['fromMe'] = 0;
		}
		unset($messageArray[ChatMessagesDao::COLUMN_ID_SENDER]); //id odesilatele je uz v prvnim klici pole
		unset($messageArray[ChatMessagesDao::COLUMN_ID_RECIPIENT]);  //neposila uzivateli jeho vlastni id
		$sendedDate = $messageArray[ChatMessagesDao::COLUMN_SENDED_DATE]->format('Y-m-d H:i:s');
		$messageArray[ChatMessagesDao::COLUMN_SENDED_DATE] = $sendedDate;
		return $messageArray;
	}

	/**
	 * Vrátí uživatelské jméno uživatele s daným id
	 * Šetří databázi.
	 * @param int $id id uživatele
	 * @return string Uzivatelske jmeno
	 */
	private function getUsername($id) {
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
	private function registerInfoAboutDelivery($idRecipient, $idMessage) {
		$session = $this->getDeliverySession();
		$session->offsetSet($idRecipient, $idMessage);
	}

	/**
	 * Přidá do pole odpovědi také informační zprávu o tom, že poslední
	 * zpráva odeslaná danému uživateli byla přečtena (pokud byla). Pokud
	 * je již uživatel v poli (tj. posílá zprávu), informační zpráva se nepřidá.
	 * V případě, že daný uživatel poslal zprávu nebo ji přečetl, se smaže požadavek
	 * na zjišťování stavu zprávy, registrovaný pomocí registerInfoAboutDelivery
	 * @param array $responseArray pole odpovědi, kam se má informace přidat
	 * @return array doplnene pole
	 */
	private function addInfoAboutDeliveredMessages($responseArray) {
		$session = $this->getDeliverySession();
		$userId = $this->getPresenter()->getUser()->getId();
		$undeliveredMessages = $this->chatManager->getAllUnreadedMessagesFromUser($userId);
		foreach ($session as $idRecipient => $idMessage) {//vsechny registrovane pozadavky o precteni
			$responseArray = $this->resolveSingleRegisteredInfoRequest($idRecipient, $idMessage, $undeliveredMessages, $session, $responseArray);
		}
		return $responseArray;
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
	 * @param array $responseArray pole na data prohlížeči
	 * @return array doplnene pole
	 */
	private function resolveSingleRegisteredInfoRequest($idRecipient, $idMessage, $undeliveredMessages, $session, $responseArray) {
		$recipientCoded = $this->chatManager->getCoder()->encodeData($idRecipient); //data v poli jsou jiz zakodovana
		if (array_key_exists($recipientCoded, $responseArray)) {//v poli je uzivatel, u nejz cekame na precteni - tj. posila zpravu
			$session->offsetUnset($idRecipient); //nepotrebujeme tedy navic informovat o precteni
		} else {//uzivatel novou zpravu neposlal
			$responseArray = $this->addInfoIfMessageWasReaded($session, $idMessage, $idRecipient, $recipientCoded, $undeliveredMessages, $responseArray);
		}
		return $responseArray;
	}

	/**
	 * Porovná žádost o info o doručení a seznam
	 * nedoručených zpráv. Podle toho pak aktualizuje session seznamu a pole s daty.
	 * @param \Nette\Http\SessionSection $session session pro seznam žádostí
	 * @param int $idMessage id zprávy
	 * @param int $idRecipient skutečné id příjemce
	 * @param int $recipientCoded kódované id příjemce v datech pro prohlížeč
	 * @param array $undeliveredMessages seznam nedoručených zpráv
	 * @param array $responseArray pole na data prohlížeči
	 * @return array doplnene pole
	 */
	private function addInfoIfMessageWasReaded($session, $idMessage, $idRecipient, $recipientCoded, $undeliveredMessages, $responseArray) {
		if (array_key_exists($idMessage, $undeliveredMessages)) {//zprava nebyla prectena
			//nedela nic
		} else {//zprava byla prectena
			$responseArray[$recipientCoded]['messages'] = array($this->createDeliveryInfoMessage());
			$responseArray[$recipientCoded]['name'] = $this->getUsername($idRecipient);
			$session->offsetUnset($idRecipient);
		}
		return $responseArray;
	}

	/**
	 * Vytvori zpravicku o tom, ze zprava byla prectena
	 * @return array zpravicka
	 */
	private function createDeliveryInfoMessage() {
		return array(
			ChatMessagesDao::COLUMN_TEXT => self::DELIVERY_MESSAGE,
			ChatMessagesDao::COLUMN_TYPE => ChatMessagesDao::TYPE_INFO_MESSAGE
		);
	}

}
