<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Rozhraní co musí implementovat každé dao, co chce používat komponentu na komentáře.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface ICommentDao {

	/**
	 * Vrátí všechny komentáře příspěvku
	 * @param int $itemID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllComments($itemID);

	/**
	 * Vrátí prvních několik komentářů příspěvku
	 * @param int $itemID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getFewComments($itemID, $limit);

	/**
	 * Vloží komentář k obrázku
	 * @param int $itemID ID příspěvku, který komentujeme
	 * @param int $userID ID uživatele co komentář napsal.
	 * @param string $comment Komentář obrázku
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($itemID, $userID, $comment, $ownerID);

	/**
	 * Přidá aktivitu komentování o položce Item (např. obrázku) vlastníkovi
	 * @param int $ownderID ID uživatele, kterému item patří
	 * @param int $creatorID ID uživatele, co lajkoval
	 * @param int $itemID ID položky, co se má lajknout
	 */
	public function addActivity($ownderID, $creatorID, $itemID);

	/**
	 * Odstraní aktivitu komentování o položce Item (např. obrázku) vlastníkovi
	 * @param int $ownderID ID uživatele, kterému item patří
	 * @param int $creatorID ID uživatele, co lajkoval
	 * @param int $itemID ID položky, co se má lajknout
	 */
	public function removeActivity($ownderID, $creatorID, $itemID);
}
