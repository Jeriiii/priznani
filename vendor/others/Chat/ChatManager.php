<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POS\Chat;

use POS\Model\ChatContactsDao;
use POS\Model\ChatMessagesDao;

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
	 * Standardni konstruktor, predani potrebnych DAO z presenteru
	 */
	function __construct(ChatContactsDao $contactsDao, ChatMessagesDao $messagesDao) {
		$this->contactsDao = $contactsDao;
		$this->messagesDao = $messagesDao;
	}

}
