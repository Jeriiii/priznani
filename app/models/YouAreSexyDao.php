<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Http\SessionSection;

/**
 * Sexy = odebírání příspěvků od uživatele bez přátelství, možnost jak
 * nenápadně naznačit že se chce spřátelit
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class YouAreSexyDao extends AbstractDao {

	const TABLE_NAME = "you_are_sexy";

	/* Column name */
	const COLUMN_ID = "id";

	/** Uživatel, který označoval */
	const COLUMN_USER_FROM_ID = "userFromID";

	/** Uživatel, který byl označen */
	const COLUMN_USER_TO_ID = "userToID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function findByUsers($userIDFrom, $userIDTo) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_FROM_ID, $userIDFrom);
		$sel->where(self::COLUMN_USER_TO_ID, $userIDTo);
		return $sel->fetch();
	}

	/**
	 * Vrátí seznam uživatelů, které uživatel označil jako sexy.
	 * @param int $userFromID
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAllFromUser($userFromID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_FROM_ID, $userFromID);

		return $sel;
	}

	/**
	 * Vrátí seznam uživatelů, kteří uživatele označili jako sexy.
	 * @param int $userToID
	 * @param int $limit
	 * @param int $offset
	 * @return \Nette\Database\Table\Selection
	 */
	public function getAllToUser($userToID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_TO_ID, $userToID);
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

	/**
	 * Spočítá, kolik lidí uživatele označili že je sexy. Cachuje se.
	 * @param int $userToID
	 * @return \Nette\Database\Table\Selection
	 */
	public function countToUser($userToID) {
		$section = $this->getUnicateSection(self::TABLE_NAME);

		if (empty($section->countToUser)) {
			$sel = $this->getAllToUser($userToID);
			$section->countToUser = $sel->count();
		}

		return $section->countToUser;
	}

	/**
	 * Přidá uživatele do seznamu co jsou sexy.
	 * @param int $userFromID Uživatel který si myslí, že je sexy.
	 * @param int $userToID Uživatel který je sexy.
	 * @return Nette\Database\Table\ActiveRow
	 * @throws \Pos\Exception\DuplicateRowException
	 */
	public function addSexy($userFromID, $userToID) {
		$rowExist = $this->findByUsers($userFromID, $userToID);
		if (!empty($rowExist)) {
			throw new \POS\Exception\DuplicateRowException;
		}

		$sel = $this->getTable();
		$sexy = $sel->insert(array(
			self::COLUMN_USER_FROM_ID => $userFromID,
			self::COLUMN_USER_TO_ID => $userToID
		));
		return $sexy;
	}

}
