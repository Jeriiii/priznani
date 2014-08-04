<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Chat;

use POS\Model\ChatContactsDao;
use POS\Model\ChatMessagesDao;
use \POS\Model\UserDao;
use \Nette\Utils\Strings;

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
	 * Kodovac a dekodovac dat
	 * @var ChatCoder
	 */
	private $coder;

	/**
	 * Standardni konstruktor, predani potrebnych DAO z presenteru
	 */
	function __construct(ChatContactsDao $contactsDao, ChatMessagesDao $messagesDao, UserDao $userDao) {
		$this->contactsDao = $contactsDao;
		$this->messagesDao = $messagesDao;
		$this->userDao = $userDao;
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
	 */
	public function sendTextMessage($idSender, $idRecipient, $text) {
		$this->messagesDao->addTextMessage($idSender, $idRecipient, $text);
	}

	/**
	 * Vrátí všechny nepřečtené zprávy daného uživatele
	 * @param int $idRecipient id uživatele
	 * @return \Nette\Database\Table\Selection zpravy
	 */
	public function getAllNewMessages($idRecipient) {
		return $this->messagesDao->getAllUnreadedTextMessages($idRecipient);
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

}
