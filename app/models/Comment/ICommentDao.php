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
	 * @param string $comment Komentář obrázku
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($itemID, $comment);
}
