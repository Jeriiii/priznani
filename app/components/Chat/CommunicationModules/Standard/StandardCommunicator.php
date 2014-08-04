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
	 * Zpracuje poslani zpravy zaslane prohlizecem ve formatu JSON
	 */
	public function handleSendMessage() {
		$json = file_get_contents("php://input");
		$data = Json::decode($json); //prijata zprava
		if ($data && !empty($data) && $data->type == 'textMessage') {//ulozeni zpravy do DB
			$sender = $this->getPresenter()->getUser()->getId();
			$data->to = (int) $this->chatManager->getCoder()->decodeData($data->to); //dekodovani id
			$this->chatManager->sendTextMessage($sender, $data->to, $data->text);

			$this->sendRefreshResponse();
		}
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
				$array[$senderCoded] = array(); //pak vytvori pole na zpravy od tohoto uzivatele
			}
			$rowArray = $row->toArray();
			$rowArray['name'] = $this->getUsername($sender); //pribaleni uzivatelskeho jmena
			$rowArray[ChatMessagesDao::COLUMN_ID_SENDER] = (int) $senderCoded; //kodovani id ve zprave
			unset($rowArray[ChatMessagesDao::COLUMN_ID_RECIPIENT]);  //neposila uzivateli jeho vlastni id
			array_push($array[$senderCoded], $rowArray); //do pole pod klicem odesilatele v poli $array vlozi pole se zpravou
		}
		return $array;
	}

	/**
	 * Vrátí uživatelské jméno uživatele s daným id
	 * Šetří databázi.
	 * @param int $id id uživatele
	 * @return String Uzivatelske jmeno
	 */
	public function getUsername($id) {
		$session = $this->getPresenter()->getSession('chat_usernames');
		$session->setExpiration(0);
		return $this->chatManager->getUsername($id, $session);
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

}
