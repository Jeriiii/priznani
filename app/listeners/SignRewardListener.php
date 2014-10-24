<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

use POS\Model\UserDao,
	Nette\Http\Session,
	DateTime;

/**
 * Description of FooListener
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class SignRewardListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/**
	 * @var Session
	 */
	public $session;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * Název sekce v sešně
	 */
	const SECTION_NAME = 'SignRewardListenerSection';

	public function __construct(Session $session, UserDao $userDao) {
		$this->session = $session;
		$this->userDao = $userDao;
		$this->session->setExpiration('30 days');
	}

	/**
	 * Implementace interfacu.
	 * @return array Vrací pole objektů a jejich událostí, nad kterými bude naslouchat
	 */
	public function getSubscribedEvents() {

		return array(
			'POS\Listeners\Services\ActivityReporter::onUserActivity' => 'onActivity'
		);
	}

	public function onActivity($userID) {
		$section = $this->session->getSection(self::SECTION_NAME);
		if (!$section->lastActivity) {//sešna je nová
		}


		$section->lastActivity = new DateTime();
	}

}
