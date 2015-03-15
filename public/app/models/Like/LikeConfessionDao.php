<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 *
 * @author Daniel Holubář
 */
class LikeConfessionDao extends BaseLikeDao implements ILikeDao {

	const TABLE_NAME = "like_confessions";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_CONFESSION_ID = "confessionID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá vazbu mezi statusem a uživatelem, který ho lajkl
	 * @param int $confessionID ID přiznání, které je lajkováno
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function addLiked($confessionID, $userID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_CONFESSION_ID => $confessionID,
			self::COLUMN_USER_ID => $userID,
		));

		/* zvýší like u statusu o jedna */
		$sel = $this->createSelection(ConfessionDao::TABLE_NAME);
		$sel->where(array(
			ConfessionDao::COLUMN_ID => $confessionID
		));
		$sel->fetch();
		$sel->update(array(
			ConfessionDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(ConfessionDao::COLUMN_LIKES . ' + 1')
		));
	}

	/**
	 * ubere vazbu mezi statusem a uživatelem, který ho lajkl
	 * @param int $confessionID ID přiznání, které je odlajkováno
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function removeLiked($confessionID, $userID) {
		/* přidá vazbu mezi statusem a uživatelem */
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_CONFESSION_ID => $confessionID,
			self::COLUMN_USER_ID => $userID,
		));
		$sel->fetch();
		$sel->delete();

		/* sníží like u statusu o jedna */
		$sel = $this->createSelection(StatusDao::TABLE_NAME);
		$sel->where(array(
			ConfessionDao::COLUMN_ID => $confessionID
		));
		$sel->fetch();
		$sel->update(array(
			ConfessionDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(ConfessionDao::COLUMN_LIKES . ' - 1')
		));
	}

	/**
	 * Zjistí, jestli byl status lajknut uživatelem
	 * @param int $userID ID uživatele, který je přihlášený
	 * @param int $confessionID ID přiznání, které se prohlíží
	 * @return boolean
	 */
	public function likedByUser($userID, $confessionID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_CONFESSION_ID => $confessionID,
		));
		$liked = $sel->fetch();

		if ($liked) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function addActivity($ownderID, $creatorID, $itemID) {
		//confession by se nemelo lajkovat
	}

	public function removeActivity($ownderID, $creatorID, $itemID) {
		//confession comment by se nemel lajkovat
	}

}
