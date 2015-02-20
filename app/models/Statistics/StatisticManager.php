<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

/**
 * Stará se o správu statistik. Je možn= ho požádat o libovolnou statistiku. Počítání
 * probíhá lazy až v momentě dotazu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\Statistics;

use Nette\Database\Table\Selection;
use POS\Model\UserDao;
use Nette\DateTime;

class StatisticManager {
	
	/** @var UserDao */
	private $userDao;
	
	public function setUserDao($userDao) {
		$this->userDao = $userDao;
	}
	
	/**
	 * Vrací objekt pro práci s registracemi a statistikami.
	 * @return RegistrationStatistics Spočítá statistiky registrovaných uživatelů
	 */
	public function getRegUsers() {
		$regStat = new RegistrationStatistics($this->userDao);
		return $regStat;
	}

}
