<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Chat;

use POS\Model\ChatMessagesDao;
use \POS\Model\UserDao;
use POS\Model\FriendDao;
use POS\Model\PaymentDao;
use Nette\Http\Session;

/**
 * Správce chatu, používaný pro obecné operace chatu, které se týkají přístupu k modelům
 * a k jiným datům.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatManager extends \Nette\Object {

	/**
	 * DAO pro kontakty
	 * @var FriendDao
	 */
	private $contactsDao;

	/**
	 * DAO pro zpravy
	 * @var ChatMessagesDao
	 */
	private $messagesDao;

	/**
	 * DAO pro uzivatele
	 * @var UserDao
	 */
	private $userDao;

	/**
	 * DAO pro platby
	 * @var PaymentDao
	 */
	private $paymentDao;

	/**
	 * Kodovac a dekodovac dat
	 * @var ChatCoder
	 */
	private $coder;

	/**
	 * Sešna k různému použití
	 * @var Session
	 */
	private $session;

	const CHAT_MINUTE_SESSION_NAME = 'minuteChatCache';

	/**
	 * Standardni konstruktor, predani potrebnych DAO z presenteru
	 */
	function __construct(FriendDao $contactsDao, ChatMessagesDao $messagesDao, UserDao $userDao, PaymentDao $paymentDao, Session $session) {
		$this->contactsDao = $contactsDao;
		$this->messagesDao = $messagesDao;
		$this->userDao = $userDao;
		$this->paymentDao = $paymentDao;
		$this->session = $session;
		$this->coder = new ChatCoder();
	}

	/**
	 * Vrati vsechny kontakty daneho uzivatele vcetne zakladnich uzivatelskych udaju
	 * @param int $userId
	 * @return \Nette\Database\Table\Selection
	 */
	public function getContacts($userId) {
		return $this->contactsDao->getUsersContactList($userId);
	}

	/**
	 * Vrátí kontakt na admina
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAdminContact() {
		$superadmin = $this->userDao->find('221');
		if (empty($superadmin)) {
			$superadmin = $this->userDao->getInRoleSuperadminLimit(1)->fetch();
		}

		return $superadmin;
	}

	/**
	 * Posle uzivateli zpravu
	 * @param int $idSender id odesilatele
	 * @param int $idRecipient id prijemce
	 * @param String $text text zpravy
	 * @return Nette\Database\Table\IRow | int | bool vytvořená zpráva
	 */
	public function sendTextMessage($idSender, $idRecipient, $text) {
		return $this->messagesDao->addTextMessage($idSender, $idRecipient, $text);
	}

	/**
	 * Vrátí všechny nepřečtené zprávy daného uživatele
	 * @param int $idRecipient id uživatele
	 * @return \Nette\Database\Table\Selection zpravy
	 */
	public function getAllUnreadedMessages($idRecipient) {
		return $this->messagesDao->getAllUnreadedTextMessages($idRecipient);
	}

	/**
	 * Vrátí všechny zprávy novější než zpráva s daným id
	 * @param int $fromId dané id zpravy
	 * @param int $idRecipient id příjemce
	 * @return \Nette\Database\Table\Selection zpravy
	 */
	public function getAllNewMessages($fromId, $idRecipient) {
		return $this->messagesDao->getAllNewerMessagesThan($fromId, $idRecipient);
	}

	/**
	 * Vrátí zprávy pro první načtení daného uživatele. Vždy vrátí nějaké relevantní zprávy.
	 * @param int $userId id daného
	 * @return \Nette\Database\Table\Selection zpravy
	 */
	public function getInitialMessages($userId) {
		return $this->messagesDao->getLastTextMessages($userId, 1);
	}

	/**
	 * Vrátí posledních několik zpráv z konverzace dvou uživatelů
	 * @param int $idSender id prvního uživatele
	 * @param int $idRecipient id druhého uživatele
	 * @return \Nette\Database\Table\Selection zprávy
	 */
	public function getLastMessagesBetween($idSender, $idRecipient) {
		return $this->messagesDao->getLastTextMessagesBetweenUsers($idSender, $idRecipient, 6);
	}

	/**
	 * Vrátí zprávy starší než ta s daným id
	 * @param int $lastId id nejstarší známé zprávy
	 * @param int $limit maximální počet vrácených zpráv
	 * @param int $idUser1 id prvního uživatele
	 * @param int idUser2 id druhého uživatele
	 * @return \Nette\Database\Table\Selection zprávy
	 */
	public function getOlderMessagesBetween($lastId, $limit, $idUser1, $idUser2) {
		return $this->messagesDao->getOlderMessagesBetween($lastId, $idUser1, $idUser2, $limit);
	}

	/**
	 * Vrátí instanci Coderu k použití
	 * @return ChatCoder
	 */
	public function getCoder() {
		return $this->coder;
	}

	/**
	 * Vrátí uživatelské jméno uživatele s daným id
	 * Šetří databázi.
	 * @param int $id id uživatele
	 * @param \Nette\Http\SessionSection $session session k ukladani jmen
	 * @return String uzivatelske jmeno
	 */
	public function getUsername($id, $session) {
		if ($session->offsetExists($id)) {
			return $session->offsetGet($id);
		} else {
			$user = $this->userDao->find($id);
			$session->offsetSet($id, $user[UserDao::COLUMN_USER_NAME]);
			return $user[UserDao::COLUMN_USER_NAME];
		}
	}

	/**
	 * Nastaví všechny zprávy s id v poli jako přečtené/nepřečtené
	 * @param array $ids neasociativni pole idček
	 * @param int $idUser id příjemce kvůli bezpečnosti
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setMessagesReaded($ids, $idUser, $readed) {
		return $this->messagesDao->setMultipleMessagesReaded($ids, $idUser, $readed);
	}

	/**
	 * Nastaví všechny zprávy s id v poli jako přečtené/nepřečtené a také označí jako přečtené všechny starší zprávy
	 * @param array $ids neasociativni pole idček
	 * @param int $idUser id příjemce kvůli bezpečnosti
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setOlderMessagesReaded($ids, $idUser, $readed) {
		$maxId = 0;
		foreach ($ids as $id) {
			if ($id > $maxId) {
				$maxId = $id;
			}
		}
		return $this->messagesDao->setAllOlderMessagesReaded($maxId, $idUser, $readed);
	}

	/**
	 * Nastaví všechny zprávy starší než dané id (včetně) od daného uživatele jako přečtené
	 * @param int $idFrom id uživatele, se kterým si píšu
	 * @param int $idRecipient id přihlášeného uživatele (pro jistotu)
	 * @param int $lastId id nejnovější přečtené zprávy
	 * @param int $readed  přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setOlderMessagesFromUserReaded($idFrom, $idRecipient, $lastId, $readed) {
		return $this->messagesDao->setOlderMessagesFromUserReaded($idFrom, $idRecipient, $lastId, $readed);
	}

	/**
	 * Vrati vsechny neprectene zpravy, ktere uzivatel ODESLAL
	 * @param int $idSender odesilatel zprav
	 * @return array pole ve tvaru id_zpravy => id_prijemce
	 */
	public function getAllUnreadedMessagesFromUser($idSender) {
		$messages = $this->messagesDao->getAllSendedAndUnreadedTextMessages($idSender);
		return $messages->fetchPairs(ChatMessagesDao::COLUMN_ID, ChatMessagesDao::COLUMN_ID_RECIPIENT);
	}

	/**
	 * Vrátí TRUE, pokud je daný uživatel platící
	 * @param int $idUser id uživatele
	 * @return bool platící
	 */
	public function isUserPaying($idUser) {
		return $this->paymentDao->isUserPaying($idUser);
	}

	/**
	 * Vrátí poslední zprávu z (téměř) všech konverzací (existuje maximum konverzací)
	 * @param int $idUser id uživatele
	 * @param int $limit limit počtu konverzací
	 * @param int $offset offset počtu konverzací
	 * @param bool $forceUpdate při TRUE ignoruje data v sešně a vynutí získání nových
	 * @return array ActiveRows jako položky pole
	 */
	public function getConversations($idUser, $limit = 20, $offset = 0, $forceUpdate = FALSE) {
		$section = $this->session->getSection(self::CHAT_MINUTE_SESSION_NAME);
		$section->setExpiration('1 minute');
		if (empty($section->lastConversations) || $forceUpdate) {
			$lastChanges = $this->messagesDao->getLastConversationMessagesIDs($idUser, $limit, $offset);
			$filteredIds = $this->filterConversationIDs($idUser, $lastChanges);
			if ($offset > 0) {//pokud se doptávám na další věci
				$section->lastConversations = $this->filterExistingIDs($filteredIds, $section->lastConversations);
			} else {
				$section->lastConversations = $filteredIds;
			}
		}
		$conversationsUsersIDs = $section->lastConversations;
		$messages = array();
		foreach ($conversationsUsersIDs as $idOfOtherUser) {
			$messages[] = $this->messagesDao->getLastTextMessagesBetweenUsers($idUser, $idOfOtherUser, 1)->fetch();
		}
		return $messages;
	}

	/**
	 * Vyfiltruje pole s ID tak, aby žádné nebylo opakováno dvakrát a mělo
	 * přednost to, které není daný uživatel
	 * @param int $idUser id uživatele, kterého chceme odfiltrovat
	 * @param Selection $lastChanges selection z tabulky zpráv s ID
	 */
	private function filterConversationIDs($idUser, $lastChanges) {
		$conversationUsers = array();
		foreach ($lastChanges as $message) {
			if ($message->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER) != $idUser) {
				$correctID = $message->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER);
			} else {
				$correctID = $message->offsetGet(ChatMessagesDao::COLUMN_ID_RECIPIENT);
			}
			if (!in_array($correctID, $conversationUsers)) {
				$conversationUsers[] = $correctID;
			}
		}
		return $conversationUsers;
	}

	/**
	 * Projde dvě pole plná ID a vrátí z nového pole jen ty hodnoty, které nebyly ve starém
	 * @param array $newPairs nové pole
	 * @param array $existing staré pole
	 * @return array nové pole bez hodnot, co byly ve starém
	 */
	private function filterExistingIDs($newPairs, $existing) {
		$returnArray = array();
		foreach ($newPairs as $id) {
			if (!empty($existing) && !in_array($id, $existing)) {
				$returnArray[] = $id;
			}
		}
		return $returnArray;
	}

	/**
	 * Vrátí uživatele s daným ID kvůli profilu
	 * @param type $id
	 */
	public function getUserWithId($id) {
		return $this->userDao->find($id);
	}

}
