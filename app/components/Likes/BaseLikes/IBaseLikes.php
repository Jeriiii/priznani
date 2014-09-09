<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

/**
 * Rozhraní pro komponenty obsluhující lajkování
 * @author Daniel Holubář
 */
interface IBaseLikes {

	public function handleSexy($userID, $imageID);

	/**
	 * Vrátí informaci, zda uživatel již dal like (= sexy)
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $imageID ID obrázku, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $imageID);
}
