<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Tabulka se zablokovanými uživateli.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserBlockedDao extends AbstractDao {

	const TABLE_NAME = "users_bloked";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_OWNER_ID = "ownerID";
	const COLUMN_BLOKED_ID = "blokedID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Blokoval uživatel $ownerId uživatele $blokedId?
	 * @param int $ownerId Id uživatele, který někoho blokuje.
	 * @param int $blokedId Id uživatele, který je blokován.
	 * @return TRUE = uživatel je blokován, jinak FALSE.
	 */
	public function isBlocked($ownerId, $blokedId) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_OWNER_ID, $ownerId);
		$sel->where(self::COLUMN_BLOKED_ID, $blokedId);

		return $this->exist($sel->fetch());
	}

	/**
	 * Vrátí blokované uživatele daného uživatele.
	 * @param int $userID Id uživatele, od kterého chceme zjistit blokované uživatele.
	 * @param int $limit
	 * @param int $offset
	 * @return Nette\Database\Table\Selection
	 */
	public function getBlokedUsers($userID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_OWNER_ID, $userID);
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel;
	}

	/**
	 * Zablokuje uživatele.
	 * @param int $ownerId Id uživatele, který někoho blokuje.
	 * @param int $blokedId Id uživatele, který je blokován.
	 */
	public function addBlocking($ownerId, $blokedId) {
		/* zruší přátelství */
		$sel = $this->createSelection(FriendDao::TABLE_NAME);
		$sel->where(FriendDao::COLUMN_USER_ID_1, $ownerId);
		$sel->where(FriendDao::COLUMN_USER_ID_2, $blokedId);
		$sel->delete();

		$sel = $this->createSelection(FriendDao::TABLE_NAME);
		$sel->where(FriendDao::COLUMN_USER_ID_1, $blokedId);
		$sel->where(FriendDao::COLUMN_USER_ID_2, $ownerId);
		$sel->delete();

		/* zjistí, zda už uživatel není blokován */
		$sel = $this->getTable();
		$sel->where(self::COLUMN_OWNER_ID, $ownerId);
		$sel->where(self::COLUMN_BLOKED_ID, $blokedId);
		$blocking = $sel->fetch();

		if ($this->exist($blocking)) {
			return $blocking;
		}

		/* zablokuje uživatele */
		$sel = $this->getTable();
		return $sel->insert(array(
				self::COLUMN_OWNER_ID => $ownerId,
				self::COLUMN_BLOKED_ID => $blokedId
		));
	}

	/**
	 * Odstraní blokování od uživatele $ownerId k uživateli $blokedId
	 * @param int $ownerId Id toho, kdo blokuje.
	 * @param int $blokedId Id toho, kdo je blokován.
	 */
	public function removeBloking($ownerId, $blokedId) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_OWNER_ID, $ownerId);
		$sel->where(self::COLUMN_BLOKED_ID, $blokedId);
		$sel->delete();
	}

}
