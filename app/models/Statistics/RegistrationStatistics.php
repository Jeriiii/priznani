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
class RegistrationStatistics implements IStatistics {

	const DAY = 'days';
	const MONTH = 'months';

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
		$days = $this->getByIntervals($fromDate, $countDays, self::DAY);

		return $days;
	}

	/**
	 * Vrací pole s počtem registrací po jednotlivých měsících.
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @param int $countMonths Počet měsíců kolik se má vrátit v poli.
	 * @return array Pole s počty registrací v jednotlivých měsících
	 */
	public function getMonthly(DateTime $fromDate, $countMonths) {
		$months = $this->getByIntervals($fromDate, $countMonths, self::MONTH);

		return $months;
	}

	/**
	 * Vrací pole s počtem registrací po danných časových intervalech (dnech, týdnech, měsících).
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @param int $countDates Počet dní kolik se má vrátit v poli.
	 * @return array Pole s počty registrací v jednotlivých dnech
	 */
	private function getByIntervals(DateTime $fromDate, $countDates, $typeOfInterval = self::DAY) {
		$days = array();

		if ($countDates <= 0) {
			throw new Exception('Interval (počet dní/týdnů/měsíců) $countDates musí být větší než nula');
		}

		$actualyDate = new DateTime($fromDate); //vytváříme si vlastní proměnnou, aby jsme neovlivnili původní
		for ($i = 0; $i < $countDates; $i++) {
			$days[] = $this->countRegByInterval($typeOfInterval, $actualyDate);
			$actualyDate->modify('+1 ' . $typeOfInterval);
		}

		return $days;
	}

	/**
	 * Vrátí počet registrací v daném časovém intervalu.
	 * @param string $typeOfInterval Den, Týden, Měsíc ...
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @return \Nette\Database\Table\Selection
	 */
	private function countRegByInterval($typeOfInterval, $fromDate) {
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
