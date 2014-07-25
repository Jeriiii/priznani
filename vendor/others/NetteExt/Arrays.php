<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt;

/**
 * Třída zlehčující práci s poli
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Arrays extends \Nette\Object {

	/**
	 * Vrátí hodnotu v poli, pokud existuje. Jinak vrátí nastavenou hodnotu.
	 * @param array $arr Prohledávané pole.
	 * @param string $key Klíč prvku v poli.
	 * @param string $return Hodnota kterou to má vrátit, když prvek v poli není nalezen.
	 */
	public static function getVal($arr, $key, $return = "") {
		if (array_key_exists($key, $arr)) {
			return $arr[$key];
		} else {
			return $return;
		}
	}

	/**
	 * Přidá do pole prvek není proměnná prázdná
	 * @param string $key
	 * @param string $value
	 * @param array $data Pole s daty.
	 * @return array
	 */
	public static function addVal($key, $value, $data = NULL) {
		if (empty($data)) {
			$data = array();
		}

		if (isset($value)) {
			$data[$key] = $value;
		}

		return $data;
	}

	/**
	 * Odstraní všechny prvky pole co neobsahují řetězec.
	 * @param array $arr Pole prvků, které se má vytřídit.
	 * @param string $contains Co musí hodnota v poli obsahovat, aby v něm zůstala.
	 * @return array Vytřízené pole.
	 */
	public static function sortOut($arr, $contains) {
		foreach ($arr as $key => $val) {
			if (strpos($val, $contains) === FALSE) {
				unset($arr[$key]);
			}
		}

		return $arr;
	}

}
