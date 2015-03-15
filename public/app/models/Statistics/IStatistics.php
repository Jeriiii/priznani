<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;

/**
 * Toto rozhraní by měl implementovat každý objekt který pracuje se statistikami
 * a má se vykreslit v komponentě Graph
 * 
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IStatistics {
	
	/**
	 * Vrací pole s počtem jednotek po jednotlivých dnech.
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vidět graf.
	 * @param int $countDays Počet dní kolik se má vrátit v poli.
	 * @return array Pole s počty jednotek v jednotlivých dnech
	 */
	public function getDaily(DateTime $fromDate, $countDays);

	/**
	 * Vrací pole s počtem jednotek po jednotlivých měsících.
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vidět graf.
	 * @param int $countDays Počet měsíců kolik se má vrátit v poli.
	 * @return array Pole s počty jednotek v jednotlivých měsících
	 */
	public function getMonthly(DateTime $fromDate, $countDays);
}
