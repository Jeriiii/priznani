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
class ScoreListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/**
	 * Skóre přidané v případě, že je uživatel označen jako sexy
	 */
	const SCORE_ADDED_IS_SEXY = 5;

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
			'POS\Model\YouAreSexyDao::onDislike' => 'onIsNotSexyAnymore'
		);
	}

	/**
	 * Při události, kdy je někdo označen jako sexy
	 * @param int $userID1 id uživatele, co like dal
	 * @param int $userID2 id uživatele, který like dostal
	 */
	public function onIsSexy($userID1, $userID2) {
		$this->userPropertyDao->incraseScoreBy($userID2, self::SCORE_ADDED_IS_SEXY);
	}

	/**
	 * Při události, kdy je někdo ODznačen jako sexy
	 * @param int $userID1 id uživatele, co like odebral
	 * @param int $userID2 id uživatele, který like ztratil
	 */
	public function onIsNotSexyAnymore($userID1, $userID2) {
		$this->userPropertyDao->decraseScoreBy($userID2, self::SCORE_ADDED_IS_SEXY);
	}

}
