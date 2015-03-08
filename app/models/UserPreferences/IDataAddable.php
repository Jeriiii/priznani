<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Rozhraní, které musí implementovat preference donačítající nová data
 * do session cache
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */

namespace POS\UserPreferences;

interface IDataAddable {

	/**
	 * Donačte nová data do session cache.
	 */
	public function addNewData();
}
