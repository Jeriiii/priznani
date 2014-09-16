<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Interface, který musí implementovat každé DAO, které chce používat
 * komponentu BaseLikes.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface ILikeDao {

	/**
	 * Zjistí, jestli byl příspěvek lajknut uživatelem
	 * @param int $userID ID uživatele, který je přihlášený
	 * @param int $itemID ID příspěvku, u kterého chceme zjistit zda byl lajknut
	 * @return boolean
	 */
	public function likedByUser($userID, $itemID);
}
