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
class CommentImagesDao extends AbstractDao implements ICommentDao {

	const TABLE_NAME = "comment_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_IMAGE_ID = "imageID";
	const COLUMN_LIKES = "likes";
	const COLUMN_COMMENT = "comment";
	const COLUMN_USER_ID = "userID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny komentáře příspěvku
	 * @param int $imageID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllComments($imageID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_IMAGE_ID, $imageID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí prvních několik komentářů příspěvku
	 * @param int $imageID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getFewComments($imageID, $limit = 2) {
		$sel = $this->getAllComments($imageID);
		return $sel->limit($limit);
	}

	/**
	 * Vloží komentář k obrázku
	 * @param int $imageID ID obrázku, který komentujeme
	 * @param int $userID ID uživatele co komentář napsal.
	 * @param string $comment komentář obrázku
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($imageID, $userID, $comment) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_COMMENT => $comment,
			self::COLUMN_USER_ID => $userID
		));

		/* zvýšení počtu komentářů u obrázku o jedna */
		$selImg = $this->createSelection(UserImageDao::TABLE_NAME);
		$selImg->wherePrimary($imageID);
		$image = $selImg->fetch();
		$image->update(array(
			UserImageDao::COLUMN_COMMENTS => $image->comments + 1
		));

		return $sel;
	}

}
