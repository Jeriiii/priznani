<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 *
 * @author Daniel Holubář
 */
class CommentStatusesDao extends BaseCommentDao {

	const TABLE_NAME = "comment_statuses";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_STATUS_ID = "statusID";
	const COLUMN_LIKES = "likes";
	const COLUMN_COMMENT = "comment";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny komentáře příspěvku
	 * @param int $statusID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllComments($statusID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_STATUS_ID, $statusID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí prvních několik komentářů příspěvku
	 * @param int $statusID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getFewComments($statusID, $limit = 2) {
		$sel = $this->getAllComments($statusID);
		return $sel->limit($limit);
	}

	/**
	 * Vloží komentář k obrázku
	 * @param int $statusID ID statusu, který komentujeme
	 * @param int $userID ID uživatele co komentář napsal.
	 * @param string $comment komentář obrázku
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($statusID, $userID, $comment, $ownerID) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_COMMENT => $comment,
			self::COLUMN_USER_ID => $userID
		));

		$this->incrementCountStatus($statusID);

		$this->addActivity($ownerID, $userID, $statusID);

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
		$this->decrementCountStatus($comment->statusID);
		$this->removeActivity($comment->status->userID, $comment->userID, $commentID);
		parent::delete($commentID);
	}

	/**
	 * Zvýšení počtu komentářů u statusu o jedna.
	 * @param int $statusID
	 */
	public function incrementCountStatus($statusID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selStat = $this->createSelection(StatusDao::TABLE_NAME);
		$selStat->wherePrimary($statusID);
		$status = $selStat->fetch();
		$status->update(array(
			StatusDao::COLUMN_COMMENTS => $status->comments + 1
		));
	}

	/**
	 * Sníží počtu komentářů u statusu o jedna.
	 * @param int $statusID
	 */
	public function decrementCountStatus($statusID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selStat = $this->createSelection(StatusDao::TABLE_NAME);
		$selStat->wherePrimary($statusID);
		$status = $selStat->fetch();
		$status->update(array(
			StatusDao::COLUMN_COMMENTS => $status->comments - 1
		));
	}

	/**
	 * Odstraní komentář z aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $status ID statusu.
	 */
	public function addActivity($ownerID, $creatorID, $status) {
		$sel = $this->getActivityTable();
		$type = "comment";
		$activity = ActivitiesDao::createStatusActivityStatic($creatorID, $ownerID, $status, $type, $sel);
		return $activity;
	}

	/**
	 * Odstraní komentář z aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $status ID statusu.
	 */
	public function removeActivity($ownerID, $creatorID, $status) {
		$sel = $this->getActivityTable();
		$type = "comment";
		$activity = ActivitiesDao::removeStatusActivityStatic($creatorID, $ownerID, $status, $type, $sel);
		return $activity;
	}

}
