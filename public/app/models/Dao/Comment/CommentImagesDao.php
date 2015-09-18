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
class CommentImagesDao extends BaseCommentDao {

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
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($imageID, $userID, $comment, $ownerID) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_COMMENT => $comment,
			self::COLUMN_USER_ID => $userID
		));

		$this->incrementCountImage($imageID);

		$this->addActivity($ownerID, $userID, $imageID);

		return $sel;
	}

	/**
	 * smaže komentář
	 * @param int $commentID ID komentáře, který má být smazán
	 */
	public function delete($commentID) {
		$sel = $this->getTable();
		$sel->wherePrimary($commentID);
		$comment = $sel->fetch();
		$this->decrementCountImage($comment->imageID);
		$this->removeActivity($comment->userID, $comment->image->gallery->userID, $commentID);
		parent::delete($commentID);
	}

	/**
	 * Zvýšení počtu komentářů u obrázku o jedna.
	 * @param int $imageID
	 */
	public function incrementCountImage($imageID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selImg = $this->createSelection(UserImageDao::TABLE_NAME);
		$selImg->wherePrimary($imageID);
		$image = $selImg->fetch();
		$image->update(array(
			UserImageDao::COLUMN_COMMENTS => $image->comments + 1
		));
	}

	/**
	 * Sníží počtu komentářů u obrázku o jedna.
	 * @param int $imageID
	 */
	public function decrementCountImage($imageID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selImg = $this->createSelection(UserImageDao::TABLE_NAME);
		$selImg->wherePrimary($imageID);
		$image = $selImg->fetch();
		$image->update(array(
			UserImageDao::COLUMN_COMMENTS => $image->comments - 1
		));
	}

	/**
	 * Odstraní komentář z aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $status ID statusu.
	 */
	public function addActivity($ownerID, $creatorID, $status) {
		if ($ownerID != 0) { //neexistuje vlastník - např. u soutěží
			$sel = $this->getActivityTable();
			$type = ActivitiesDao::TYPE_COMMENT;
			$activity = ActivitiesDao::createImageActivityStatic($creatorID, $ownerID, $status, $type, $sel);
			return $activity;
		}
		return NULL;
	}

	/**
	 * Odstraní komentář z aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $status ID statusu.
	 */
	public function removeActivity($ownerID, $creatorID, $status) {
		if ($ownerID != 0) { //neexistuje vlastník - např. u soutěží
			$sel = $this->getActivityTable();
			$type = ActivitiesDao::TYPE_COMMENT;
			$activity = ActivitiesDao::removeImageActivityStatic($creatorID, $ownerID, $status, $type, $sel);
		}
	}

}
