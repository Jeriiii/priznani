<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use \Nette\Utils\Json;
use POS\Model\ChatMessagesDao;
use Nette\Database\Table\IRow;
use POS\Ext\LastActive;

/**
 * Slouží přímo ke komunikaci mezi serverem a prohlížečem, zpracovává
 * požadavky a vrací odpovědi. Veškerá komunikace ajaxem probíhá zde.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class AndroidCommunicator extends BaseChatComponent implements IRemoteCommunicator {

	/** Maximální počet konverzací odeslaných do mobilu */
	const LIMIT_OF_CONVERSATIONS = 10;
	const TAG_CONVERSATIONS = 'conversations';
	const TAG_CONVERSATION = 'conversation';
	const TAG_USER = 'user';

	/**
	 * Jmeno session, kam se ukladaji jmena uzivatelu
	 */
	const USERNAMES_SESSION_NAME = 'chat_usernames';

	public function render() {
		/* no render */
	}

	public function handleGetConversations() {
		$userId = $this->getPresenter()->getUser()->getId();
		$conversations = $this->chatManager->getConversations($userId, self::LIMIT_OF_CONVERSATIONS);
		$this->getPresenter()->sendJson($this->convertConversationsToJson($conversations));
	}

	/**
	 * Vrátí informace o konverzaci jednoho uživatele s přihlášeným uživatelem a poslední zprávy
	 * @param int $fromId druhý uživatel
	 */
	public function handleGetSingleConversation($fromId) {
		$realId = $this->chatManager->getCoder()->decodeData($fromId);
		$userId = $this->getPresenter()->getUser()->getId();
		$userDb = $this->chatManager->getUserWithId($realId);
		$messages = $this->chatManager->getLastMessagesBetween($userId, $realId);
		$user = array(
			'id' => $fromId,
			'name' => $userDb->user_name,
			'lastActive' => LastActive::format($userDb->last_active)
		);
		$response = array(
			self::TAG_CONVERSATION => $this->convertMessagesToJson($messages),
			self::TAG_USER => $user
		);
		$this->getPresenter()->sendJson($response);
	}

	/**
	 * Překonvertuje pole ActiveRows konverzací do obyčejného pole s potřebnými daty
	 * @param array $conversations pole konverzací
	 */
	private function convertConversationsToJson($conversations) {
		$json = array();
		foreach ($conversations as $conversation) {
			array_push($json, array(
				'from' => $this->getCorrectUsername($conversation->id_sender, $conversation->id_recipient),
				'fromId' => $this->getCorrectCodedId($conversation->id_sender, $conversation->id_recipient),
				'readed' => $conversation->readed != 0 ? 'true' : 'false',
				'lastMessage' => $conversation->text
			));
		}
		return array(self::TAG_CONVERSATIONS => $json);
	}

	/**
	 * Vrátí přihlašovací jméno, které souvisí s uživatelem, s nímž si píše
	 * přihlášený uživatel
	 * @param int $idSender id odesílatele
	 * @param int $idRecipient
	 * @return string jméno příjemce
	 */
	private function getCorrectUsername($idSender, $idRecipient) {
		$session = $this->getPresenter()->getSession(self::USERNAMES_SESSION_NAME);
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		if ($idSender == $loggedUserId) {
			return $this->chatManager->getUsername($idRecipient, $session);
		} else {
			return $this->chatManager->getUsername($idSender, $session);
		}
	}

	/**
	 * Vrátí ze dvou id to správné uživatelské ID, které není přihlášený uživatel
	 * @param type $idSender první id
	 * @param type $idRecipient druhé id
	 * @return type
	 */
	private function getCorrectCodedId($idSender, $idRecipient) {
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		$coder = $this->chatManager->getCoder();
		if ($idSender == $loggedUserId) {
			return $coder->encodeData($idRecipient);
		} else {
			return $coder->encodeData($idSender);
		}
	}

	/**
	 * Převede výběr z tabulky zpráv na pole, které bude možné odeslat zpět prohlížeči
	 * @param Selection $messages vyber z tabulky chat_messages
	 * @return array pole ve formatu pro JSON ve tvaru dokumentace chatu
	 */
	private function convertMessagesToJson($messages) {
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
	 * Zpracuje poslani zpravy zaslane prohlizecem ve formatu JSON
	 */
	public function handleSendMessage($toId, $text, $type) {
		$user = $this->getPresenter()->getUser();
		if ($type == 'textMessage' && $user->isLoggedIn()) {//ulozeni zpravy do DB
			$senderId = $user->getId();
			$toId = (int) $this->chatManager->getCoder()->decodeData($toId); //dekodovani id
			$message = $this->chatManager->sendTextMessage($senderId, $toId, $text); //ulozeni zpravy
			$this->getPresenter()->sendJson(array(
				'id' => $message->id,
				'senderName' => $this->getUsername($user->getId()),
				'sendedDate' => $message->sendedDate->format('d.m.Y H:i')
			));
		}
	}

	public function handleGetOlderMessages($lastId, $limit, $withUserId) {
		$toId = (int) $this->chatManager->getCoder()->decodeData($withUserId); //dekodovani id
		$messages = $this->chatManager->getOlderMessagesBetween($lastId, $limit, $this->getPresenter()->getUser()->getId(), $toId);
		$jsonMessages = $this->convertMessagesToJson($messages);
		$this->getPresenter()->sendJson(array('oldermessages' => empty($jsonMessages) ? NULL : $jsonMessages));
	}

	/**
	 * Vyřízení žádosti o poslání nových zpráv (prohlížeč se ptá serveru, zda nejsou nějaké nové zprávy)
	 * @param int $lastid posledni zname id
	 * @param json $readedmessages pole idcek prectenych zprav
	 */
	public function handleRefreshMessages($lastId) {
		$user = $this->getPresenter()->getUser();
		if ($user->isLoggedIn()) {
			$this->sendRefreshResponse($lastId);
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
		$this->getPresenter()->sendJson(array(
			'newMessages' => empty($response) ? NULL : $response,
			'unreadedMessages' => $this->chatManager->getAllUnreadedMessages($userId)->count()
		));
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

		$sendedDate = $messageArray[ChatMessagesDao::COLUMN_SENDED_DATE]->format('d.m. Y H:i');
		$messageArray[ChatMessagesDao::COLUMN_SENDED_DATE] = $sendedDate;

		return $messageArray;
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

}
