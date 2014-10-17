<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

/**
 * Description of FooListener
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class CoinListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/**
	 * Množství zlatek přidaných v případě, že je uživatel označen jako sexy
	 */
	const COIN_ADDED_IS_SEXY = 5;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	public function __construct(\POS\Model\UserPropertyDao $propertyDao, \POS\Model\UserDao $userDao) {
		$this->userDao = $userDao;
		$this->userPropertyDao = $propertyDao;
	}

	/**
	 * Implementace interfacu.
	 * @return array Vrací pole objektů a jejich událostí, nad kterými bude naslouchat
	 */
	public function getSubscribedEvents() {

		return array('Nette\Application\Application::onStartup' => 'onIsSexy');
	}

	/**
	 * Při události, kdy je někdo označen jako sexy
	 * @param int $userID1 id uživatele, co like dal
	 * @param int $userID2 id uživatele, který like dostal
	 */
	public function onIsSexy($userID1) {
		$this->userPropertyDao->decraseCoinsBy(4, self::COIN_ADDED_IS_SEXY);
	}

	/**
	 * Při události, kdy je někdo ODznačen jako sexy
	 * @param int $userID1 id uživatele, co like odebral
	 * @param int $userID2 id uživatele, který like ztratil
	 */
	public function onIsNotSexyAnymore($userID1, $userID2) {
		$this->userPropertyDao->incraseCoinsBy($userID2, self::COIN_ADDED_IS_SEXY);
	}

}
