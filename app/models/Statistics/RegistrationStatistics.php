<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;
use POS\Model\UserDao;

/**
 * Spočítá statistiky registrovaných uživatelů
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class RegistrationStatistics {

	/** @var \POS\Model\UserDao @inject */
	private $userDao;

	public function __construct(UserDao $userDao) {
		$this->userDao = $userDao;
	}

	/**
	 * Vrací pole s počtem registrací po jednotlivých dnech.
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @param int $countDays Počet dní kolik se má vrátit v poli.
	 * @return array Pole s počty registrací v jednotlivých dnech
	 */
	public function getDaily(DateTime $fromDate, $countDays) {
		$days = array();

		if ($countDays <= 0) {
			throw new Exception('Interval (počet dní) $countDays musí být větší než nula');
		}

		for ($i = 0; $i < $countDays; $i++) {
			$days[] = $this->userDao->countRegByDay($fromDate);
			$fromDate->modify('+1 day');
		}

		return $days;
	}

}
