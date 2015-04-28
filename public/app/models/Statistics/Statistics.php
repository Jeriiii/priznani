<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;

/**
 * Třída pro počítání statistik
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
abstract class Statistics implements IStatistics {

	const DAY = 'days';
	const MONTH = 'months';

	/**
	 * Vrací pole s počtem položek po jednotlivých dnech. Pokud tedy chceme
	 * vybrat např. týden, zvolíme počíteční datum a $countDays bude 7.
	 * @param DateTime $fromDate Počáteční datum od kdy chci tvořit statistiku.
	 * @param int $countDays Počet dní, které se promítnou ve statistice.
	 * @return array Pole s počty položek v jednotlivých dnech
	 */
	public function getDaily(DateTime $fromDate, $countDays) {
		$days = $this->getByIntervals($fromDate, $countDays, self::DAY);

		return $days;
	}

	/**
	 * Vrací pole s počtem položek po jednotlivých měsících.
	 * @param DateTime $fromDate Počáteční datum od kdy chci tvořit statistiku.
	 * @param int $countMonths Počet měsíců, které se promítnou ve statistice.
	 * @return array Pole s počty položek v jednotlivých měsících
	 */
	public function getMonthly(DateTime $fromDate, $countMonths) {
		$months = $this->getByIntervals($fromDate, $countMonths, self::MONTH);

		return $months;
	}

	/**
	 * Vrací pole s počtem položek po danných časových intervalech (dnech, týdnech, měsících).
	 * @param DateTime $fromDate Počáteční datum od kdy chci tvořit statistiku.
	 * @param int $countDates Počet dnů, měsíců ..., které se promítnou ve statistice.
	 * @return array Pole s počty položek v jednotlivých dnech
	 */
	protected function getByIntervals(DateTime $fromDate, $countDates, $typeOfInterval = self::DAY) {
		$days = array();

		if ($countDates <= 0) {
			throw new Exception('Interval (počet dní/týdnů/měsíců) $countDates musí být větší než nula');
		}

		$actualyDate = new DateTime($fromDate); //vytváříme si vlastní proměnnou, aby jsme neovlivnili původní
		for ($i = 0; $i < $countDates; $i++) {
			$days[] = $this->countItemsByInterval($typeOfInterval, $actualyDate);
			$actualyDate->modify('+1 ' . $typeOfInterval);
		}

		return $days;
	}

}
