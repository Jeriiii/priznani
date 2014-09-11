<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * LikeStatus DAO
 * slouží k práci s vazební tabulkou na lajkování statusů
 *
 * @author Daniel Holubář
 */
class LikeStatusDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "like_statuses";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_STATUS_ID = "statusID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá vazbu mezi statusem a uživatelem, který ho lajkl
	 * @param int $statusID ID statusu, který je lajkován
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function addLiked($statusID, $userID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_USER_ID => $userID,
		));

		/* zvýší like u statusu o jedna */
		$sel = $this->createSelection(StatusDao::TABLE_NAME);
		$sel->where(array(
			StatusDao::COLUMN_ID => $statusID
		));
		$sel->fetch();
		$sel->update(array(
			StatusDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(StatusDao::COLUMN_LIKES . ' + 1')
		));
	}

	/**
	 * ubere vazbu mezi statusem a uživatelem, který ho lajkl
	 * @param int $statusID ID statusu, který je odlajkován
	 * @param int $userID ID uživatele, který lajkuje
	 */
	public function removeLiked($statusID, $userID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_USER_ID => $userID,
		));
		$sel->fetch();
		$sel->delete();

		/* sníží like u statusu o jedna */
		$sel = $this->createSelection(StatusDao::TABLE_NAME);
		$sel->where(array(
			StatusDao::COLUMN_ID => $statusID
		));
		$sel->fetch();
		$sel->update(array(
			StatusDao::COLUMN_LIKES => new \Nette\Database\SqlLiteral(StatusDao::COLUMN_LIKES . ' - 1')
		));
	}

	/**
	 * Zjistí, jestli byl status lajknut uživatelem
	 * @param int $userID ID uživatele, který je přihlášený
	 * @param int $statusID ID statusu, který se prohlíží
	 * @return boolean
	 */
	public function likedByUser($userID, $statusID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_STATUS_ID => $statusID,
		));
		$liked = $sel->fetch();

		if ($liked) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
