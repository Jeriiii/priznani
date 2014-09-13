<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * CommentImages Dao
 * slouží k práci s komentáři obrázků
 *
 * @author Daniel Holubář
 */
class CommentImagesDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "comment_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_IMAGE_ID = "imageID";
	const COLUMN_LIKES = "likes";
	const COLUMN_COMMENT = "comment";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny komentáře obrázku
	 * @param int $ID ID obrázku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllImageComments($ID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_IMAGE_ID => $ID,
		));
		return $sel->order(self::COLUMN_ID . ' DESC');
	}

	/**
	 * Vrátí dva komentáře obrázku
	 * @param int $ID ID obrázku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getTwoNewestComments($ID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_IMAGE_ID => $ID,
		));
		$sel->order(self::COLUMN_ID . ' DESC');
		return $sel->limit(2);
	}

	/**
	 * Vloží komentář k obrázku
	 * @param int $ID ID obrázku, který komentujeme
	 * @param string $comment komentář obrázku
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($ID, $comment) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_IMAGE_ID => $ID,
			self::COLUMN_COMMENT => $comment,
		));

		return $sel;
	}

}
