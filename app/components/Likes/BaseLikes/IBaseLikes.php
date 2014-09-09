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

	/**
	 * Signál pro provedení lajku, přičte lajk obrázku a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl obrázek
	 * @param int $imageID ID lajknutého obrázku
	 */
	public function handleSexy($userID, $imageID);

	/**
	 * Vrátí informaci, zda uživatel již dal like (= sexy)
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $imageID ID obrázku, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $imageID);
}
