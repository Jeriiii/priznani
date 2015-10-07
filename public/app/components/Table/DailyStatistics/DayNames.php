<?php

namespace JKB\Component\Statistics\Daily;

use DateTime;

/**
 * Vrací název sloupců. Pro každý sloupec v tabulce je nalezen den v týdnu,
 * který k němu náleží.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DayNames {

	/**
	 * Vrátí názvy dní v jednotlivých datumech.
	 * @return array Názvy dní.
	 */
	public function getDayNames() {
		$dayNames = array();
		$dayNames[0] = 'dnes';
		$dayNames[1] = 'včera';

		$day = new DateTime;
		$day->modify('- 2 days');

		for ($i = 2; $i != 8; $i++) {
			$dayNames[] = $this->czNameDay($day->format('w')) . ' ' . $day->format('d.m.') . '';
			$day->modify('- 1 day');
		}

		return $dayNames;
	}

	/**
	 * Vrácení českého názvu dne v týdnu
	 * @param int 0-6, 0 neděle
	 * @return string
	 * @copyright Jakub Vrána, http://php.vrana.cz/
	 */
	protected function czNameDay($den) {
		static $nazvy = array('NE', 'PO', 'ÚT', 'ST', 'ČT', 'PÁ', 'SO');
		return $nazvy[$den];
	}

}
