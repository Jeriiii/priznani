<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\SqlLiteral;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class LikeStatusCommentDao extends AbstractDao implements ILikeDao {

	const TABLE_NAME = "like_status_comments";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_COMMENT_ID = "commentID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá vazbu mezi commentem a uživatelem, který ho lajkl
	 * @param int $commentID ID statusu, který je lajkován
	 * @param int $userID ID uživatele, který lajkuje
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 */
	public function addLiked($commentID, $userID, $ownerID) {
		/* přidá vazbu mezi commentem a uživatelem */
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_COMMENT_ID => $commentID,
			self::COLUMN_USER_ID => $userID,
		));

		/* zvýší like u statusu o jedna */
		$sel = $this->createSelection(CommentStatusesDao::TABLE_NAME);
		$sel->where(array(
			CommentStatusesDao::COLUMN_ID => $commentID
		));
		$sel->update(array(
			CommentStatusesDao::COLUMN_LIKES => new SqlLiteral(CommentStatusesDao::COLUMN_LIKES . ' + 1')
		));
		$commentLike = $sel->fetch();

		$this->addActivity($ownerID, $userID, $commentLike->commentID);
	}

	/**
	 * ubere vazbu mezi commentum a uživatelem, který ho lajkl
	 * @param int $commentID ID commentu, který je odlajkován
	 * @param int $userID ID uživatele, který lajkuje
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 */
	public function removeLiked($commentID, $userID, $ownerID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_COMMENT_ID => $commentID,
			self::COLUMN_USER_ID => $userID,
		));
		$sel->fetch();
		$sel->delete();

		/* sníží like u statusu o jedna */
		$sel = $this->createSelection(CommentStatusesDao::TABLE_NAME);
		$sel->where(array(
			StatusDao::COLUMN_ID => $commentID
		));
		$sel->update(array(
			CommentStatusesDao::COLUMN_LIKES => new SqlLiteral(CommentStatusesDao::COLUMN_LIKES . ' - 1')
		));
		$commentLike = $sel->fetch();

		$this->removeActivity($ownerID, $userID, $commentLike->commentID);
	}

	/**
	 * Zjistí, jestli byl comment lajknut uživatelem
	 * @param int $userID ID uživatele, který je přihlášený
	 * @param int $commentID ID commentu, který se prohlíží
	 * @return boolean
	 */
	public function likedByUser($userID, $commentID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_COMMENT_ID => $commentID,
		));
		$liked = $sel->fetch();

		if ($liked) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Přidá lajk do aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $commentID ID obrázku.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function addActivity($ownerID, $creatorID, $commentID) {
		$sel = $this->getActivityTable();
		$type = "like";
		$activity = ActivitiesDao::createImageActivityStatic($creatorID, $ownerID, $commentID, $type, $sel);
		return $activity;
	}

	public function removeActivity($ownderID, $creatorID, $itemID) {

	}

}
