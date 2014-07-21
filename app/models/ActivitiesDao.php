<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Aktivity ActivitiesDao
 * slouží k práci s aktivitami
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ActivitiesDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "activities";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_EVENT_TYPE = "event_type";
	const COLUMN_IMAGE_ID = "imageID";
	const COLUMN_STATUS_ID = "statusID";
	const COLUMN_EVENT_OWNER_ID = "event_ownerID";
	const COLUMN_EVENT_CREATOR_ID = "event_creatorID";

	private function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá aktivitu pro obrázek.
	 * @param type int $creatorID ID uživatele, který aktivitu provádí
	 * @param type int $ownerID ID uživatele vlastnícího obrázek
	 * @param type int $imageID ID obrázku
	 * @param type string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createImageActivity($creatorID, $ownerID, $imageID, $type) {
		$sel = $this->getTable();
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Přidá aktivitu pro status.
	 * @param type int $creatorID ID uživatele, který aktivitu provádí
	 * @param type int $ownerID ID uživatele vlastnícího status
	 * @param type int $statusID ID statusu
	 * @param type string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createStatusActivity($creatorID, $ownerID, $statusID, $type) {
		$sel = $this->getTable();
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Přidá aktivitu pro uživatele.
	 * @param type int $creatorID ID uživatele, který aktivitu provádí
	 * @param type int $ownerID ID uživatele, kterého se aktivita týká
	 * @param type string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createUserActivity($creatorID, $ownerID, $type) {
		$sel = $this->getTable();
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

}
