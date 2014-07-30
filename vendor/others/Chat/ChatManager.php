<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POS\Chat;

use POS\Model\ChatContactsDao;
use POS\Model\ChatMessagesDao;
use \POS\Model\UserDao;

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
	 * Standardni konstruktor, predani potrebnych DAO z presenteru
	 */
	function __construct(ChatContactsDao $contactsDao, ChatMessagesDao $messagesDao, UserDao $userDao) {
		$this->contactsDao = $contactsDao;
		$this->messagesDao = $messagesDao;
		$this->userDao = $userDao;
	}

	public function getContacts($userId) {
		return $this->contactsDao->getUsersContactList($userId);
	}

}
