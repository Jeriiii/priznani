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

	public function __construct() {
		;
	}

	/**
	 * Vrací pole s počtem registrací po jednotlivých dnech.
	 * @param UserDao $userDao
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @param int $countDays Počet dní kolik se má vrátit v poli.
	 * @return array Pole s počty registrací v jednotlivých dnech
	 */
	public function getRegUsersByDay(UserDao $userDao, DateTime $fromDate, $countDays) {
		$regStat = new RegistrationStatistics($userDao);
		$dailyStat = $regStat->getDaily($fromDate, $countDays);
		return $dailyStat;
	}

}
