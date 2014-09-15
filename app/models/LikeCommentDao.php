<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * LikeCommentDao
 * slouží k práci s lajkováním komentářů
 *
 * @author Daniel Holubář
 */
class LikeCommentDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "like_comments";

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

		/* zvýší like u statusu o jedna */
		$sel = $this->createSelection(CommentImagesDao::TABLE_NAME);
		$sel->where(array(
			CommentImagesDao::COLUMN_ID => $commentID
		));
		$sel->fetch();
		$sel->update(array(
			CommentImagesDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(CommentImagesDao::COLUMN_LIKES . ' + 1')
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
		$sel = $this->createSelection(CommentImagesDao::TABLE_NAME);
		$sel->where(array(
			StatusDao::COLUMN_ID => $commentID
		));
		$sel->fetch();
		$sel->update(array(
			CommentImagesDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(CommentImagesDao::COLUMN_LIKES . ' - 1')
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
