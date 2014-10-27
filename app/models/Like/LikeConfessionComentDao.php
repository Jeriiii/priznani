<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 *
 * @author Daniel Holubář
 */
class LikeConfessionCommentDao extends AbstractDao implements ILikeDao {

	const TABLE_NAME = "like_confession_comments";

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
	 */
	public function addLiked($commentID, $userID) {
		/* přidá vazbu mezi commentem a uživatelem */
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_COMMENT_ID => $commentID,
			self::COLUMN_USER_ID => $userID,
		));

		/* zvýší like u komentáře přiznání o jedna */
		$sel = $this->createSelection(CommentConfessionsDao::TABLE_NAME);
		$sel->where(array(
			CommentConfessionsDao::COLUMN_ID => $commentID
		));
		$sel->fetch();
		$sel->update(array(
			CommentConfessionsDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(CommentConfessionsDao::COLUMN_LIKES . ' + 1')
		));
	}

	/**
	 * ubere vazbu mezi commentum a uživatelem, který ho lajkl
	 * @param int $commentID ID commentu, který je odlajkován
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function removeLiked($commentID, $userID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_COMMENT_ID => $commentID,
			self::COLUMN_USER_ID => $userID,
		));
		$sel->fetch();
		$sel->delete();

		/* sníží like u statusu o jedna */
		$sel = $this->createSelection(CommentConfessionsDao::TABLE_NAME);
		$sel->where(array(
			StatusDao::COLUMN_ID => $commentID
		));
		$sel->fetch();
		$sel->update(array(
			CommentConfessionsDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(CommentConfessionsDao::COLUMN_LIKES . ' - 1')
		));
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

}
