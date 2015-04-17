<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 17.4.2015
 */

namespace POS\Ext;

use Nette\DateTime;

/**
 * Čas poslední aktivity uživatele.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class LastActive {

	public static function format($lastActive, $sep = array('first' => "(", 'last' => ")")) {
		if ($lastActive === NULL) {
			return "";
		}

		if (is_string($lastActive)) {
			$lastActive = new DateTime($lastActive);
		}
		$now = new DateTime();

		$diffLastActive = $now->diff($lastActive);

		if ($diffLastActive->m > 0) {
			$diffLastActive = " ";
		} else if ($diffLastActive->d > 0) {
			$diffLastActive = $sep['first'] . $diffLastActive->d . " d" . $sep['last'];
		} else if ($diffLastActive->h > 0) {
			$diffLastActive = $sep['first'] . $diffLastActive->h . " h" . $sep['last'];
		} else if ($diffLastActive->i < 2) { //když tu byl naposledy před 2 minutami, prohlásíme ho za online
			$diffLastActive = "online";
		} else {
			$diffLastActive = $sep['first'] . $diffLastActive->i . " m" . $sep['last'];
		}

		return $diffLastActive;
	}

	/**
	 * Vrati separator pro format
	 * @param string $first Text pred casem.
	 * @param string $last Text za casem.
	 * @return array
	 */
	public static function getSep($first, $last) {
		return array('first' => $first, 'last' => $last);
	}

}
