<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

use POS\Model\UserDao,
	POS\Model\UserPropertyDao,
	POS\Model\ChatMessagesDao;

/**
 * Description of FooListener
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class CoinListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/**
	 * Množství zlatek přidaných v případě, že je uživatel označen jako sexy
	 */
	const COIN_ADDED_IS_SEXY = 1;

	/**
	 * Množství zlatek pro toho, kdo označí někoho jako že je sexy
	 */
	const COIN_ADDED_FOR_LIKING = 0.2;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\ChatMessagesDao
	 * @inject
	 */
	public $chatMessagesDao;

	/**
	 * @var POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	public function __construct(UserPropertyDao $propertyDao, UserDao $userDao, ChatMessagesDao $chatMessagesDao) {
		$this->userDao = $userDao;
		$this->userPropertyDao = $propertyDao;
		$this->chatMessagesDao = $chatMessagesDao;
	}

	/**
	 * Implementace interfacu.
	 * @return array Vrací pole objektů a jejich událostí, nad kterými bude naslouchat
	 */
	public function getSubscribedEvents() {

		return array('POS\Model\YouAreSexyDao::onLike' => 'onIsSexy',
			'POS\Model\YouAreSexyDao::onDislike' => 'onIsNotSexyAnymore');
	}

	/**
	 * Při události, kdy je někdo označen jako sexy
	 * @param int $userID1 id uživatele, co like dal
	 * @param int $userID2 id uživatele, který like dostal
	 */
	public function onIsSexy($userID1, $userID2) {
		$this->userPropertyDao->incraseCoinsBy($userID2, self::COIN_ADDED_IS_SEXY);
		$this->userPropertyDao->incraseCoinsBy($userID1, self::COIN_ADDED_FOR_LIKING);
	}

	/**
	 * Při události, kdy je někdo ODznačen jako sexy
	 * @param int $userID1 id uživatele, co like odebral
	 * @param int $userID2 id uživatele, který like ztratil
	 */
	public function onIsNotSexyAnymore($userID1, $userID2) {
		$this->userPropertyDao->decraseCoinsBy($userID2, self::COIN_ADDED_IS_SEXY);
		$this->userPropertyDao->decraseCoinsBy($userID1, self::COIN_ADDED_FOR_LIKING);
	}

	public function addCoinsForMessages() {
		
	}

}
