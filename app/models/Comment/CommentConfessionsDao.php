<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 *
 * @author Daniel Holubář
 */
class CommentConfessionsDao extends AbstractDao implements ICommentDao {

	const TABLE_NAME = "comment_confessions";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_CONFESSION_ID = "confessionID";
	const COLUMN_LIKES = "likes";
	const COLUMN_COMMENT = "comment";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny komentáře příspěvku
	 * @param int $confessionID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllComments($confessionID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_CONFESSION_ID, $confessionID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí prvních několik komentářů příspěvku
	 * @param int $confessionID ID příspěvku, jehož komentáře chceme
	 * @return Nette\Database\Table\Selection
	 */
	public function getFewComments($confessionID, $limit = 2) {
		$sel = $this->getAllComments($confessionID);
		return $sel->limit($limit);
	}

	/**
	 * Vloží komentář k obrázku
	 * @param int $confessionID ID přiznání, který komentujeme
	 * @param int $userID ID uživatele co komentář napsal.
	 * @param string $comment komentář přiznání
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertNewComment($confessionID, $userID, $comment) {
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_CONFESSION_ID => $confessionID,
			self::COLUMN_COMMENT => $comment,
			self::COLUMN_USER_ID => $userID
		));

		$this->incrementCountStatus($confessionID);

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
		$this->decrementCountStatus($comment->confessionID);
		parent::delete($commentID);
	}

	/**
	 * Zvýšení počtu komentářů u přiznání o jedna.
	 * @param int $confessionID
	 */
	public function incrementCountStatus($confessionID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selConf = $this->createSelection(ConfessionDao::TABLE_NAME);
		$selConf->wherePrimary($confessionID);
		$confession = $selConf->fetch();
		$confession->update(array(
			ConfessionDao::COLUMN_COMMENTS => $confession->comments + 1
		));
	}

	/**
	 * Sníží počtu komentářů u přiznání o jedna.
	 * @param int $confessionID
	 */
	public function decrementCountStatus($confessionID) {
		/* zvýšení počtu komentářů u obrázku o jedna */
		$selConf = $this->createSelection(ConfessionDao::TABLE_NAME);
		$selConf->wherePrimary($confessionID);
		$confession = $selConf->fetch();
		$confession->update(array(
			ConfessionDao::COLUMN_COMMENTS => $confession->comments - 1
		));
	}

}
