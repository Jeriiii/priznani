<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Chat;

use POS\Model\ChatContactsDao;
use POS\Model\ChatMessagesDao;
use \POS\Model\UserDao;
use \Nette\Utils\Strings;
use POS\Model\PaymentDao;

/**
 *
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatManager {

	/**
	 * DAO pro kontakty
	 * @var ChatContactsDao
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
	 * Standardni konstruktor, predani potrebnych DAO z presenteru
	 */
	function __construct(ChatContactsDao $contactsDao, ChatMessagesDao $messagesDao, UserDao $userDao, PaymentDao $paymentDao) {
		$this->contactsDao = $contactsDao;
		$this->messagesDao = $messagesDao;
		$this->userDao = $userDao;
		$this->paymentDao = $paymentDao;
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
	 * Returns an instance of coder for security
	 * @return ChatCoder
	 */
	public function getCoder() {
		return $this->coder;
	}

	/**
	 * Oznaci vyber zprav za prectene
	 * @param \Nette\Database\Table\Selection $messages
	 */
	public function readMessages($messages) {
		$this->messagesDao->setMessagesReaded($messages, TRUE);
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

}
