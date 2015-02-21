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
class RegistrationStatistics extends Statistics {

	/** @var \POS\Model\UserDao @inject */
	private $userDao;

	public function __construct(UserDao $userDao) {
		$this->userDao = $userDao;
	}

	/**
	 * Vrátí počet registrací v daném časovém intervalu.
	 * @param string $typeOfInterval Den, Týden, Měsíc ...
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @return \Nette\Database\Table\Selection
	 */
	public function countRegByInterval($typeOfInterval, $fromDate) {
		switch ($typeOfInterval) {
			case self::DAY:
				return $this->userDao->countRegByDay($fromDate);
			case self::MONTH:
				return $this->userDao->countRegByMonth($fromDate);
			default:
				throw new Exception('$typeOfInterval must by interval constant.');
		}
	}

}
