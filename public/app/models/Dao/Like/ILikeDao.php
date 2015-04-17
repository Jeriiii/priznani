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

	/**
	 * Přidá aktivitu o položce Item (např. obrázku) vlastníkovi
	 * @param int $ownderID ID uživatele, kterému item patří
	 * @param int $creatorID ID uživatele, co lajkoval
	 * @param int $itemID ID položky, co se má lajknout
	 */
	public function addActivity($ownderID, $creatorID, $itemID);

	/**
	 * Odstraní aktivitu o položce Item (např. obrázku) vlastníkovi
	 * @param int $ownderID ID uživatele, kterému item patří
	 * @param int $creatorID ID uživatele, co lajkoval
	 * @param int $itemID ID položky, co se má lajknout
	 */
	public function removeActivity($ownderID, $creatorID, $itemID);
}
