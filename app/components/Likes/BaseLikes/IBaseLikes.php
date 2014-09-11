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
	 * Signál pro provedení lajku, přičte lajk obrázku/statusu a zaznamená, kdo lajkl
	 * @param int $userID ID uživatele, který lajkl obrázek
	 * @param int $ID ID lajknutého obrázku/statusu
	 */
	public function handleLike($userID, $ID);

	/**
	 * Vrátí informaci, zda uživatel již dal like (= sexy)
	 * @param int $userID ID uživatele, kterého hledáme
	 * @param int $ID ID obrázku/statusu, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $ID);
}
