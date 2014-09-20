<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Aktivity ActivitiesDao
 * slouží k práci s aktivitami
 *
 * @author Daniel Holubář
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
	const COLUMN_VIEWED = "viewed";

	private function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá aktivitu pro obrázek.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího obrázek
	 * @param int $imageID ID obrázku
	 * @param string $type Typ aktivity (like, comment, ...)
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
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího status
	 * @param int $statusID ID statusu
	 * @param string $type Typ aktivity (like, comment, ...)
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
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
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

	/**
	 * Vrátí aktivity pro jednoho usera.
	 * @param int $userID
	 * @return Nette\Database\Table\Selection
	 */
	public function getActivitiesByUserId($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_EVENT_OWNER_ID, $userID);
		return $sel->order(self::COLUMN_ID . ' ASC');
	}

	/**
	 * 	Vrátí počet nepřčtených aktivit dného usera
	 * @param $userID ID vlastníka aktivit
	 * @return int
	 */
	public function getCountOfUnviewed($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_EVENT_OWNER_ID, $userID);
		$sel->where(self::COLUMN_VIEWED, 0);
		return $sel->count();
	}

	/**
	 * Označí danou aktivitu za přečtenou
	 * @param int $activityID ID aktivity
	 */
	public function markViewed($activityID) {
		$sel = $this->getTable();
		$sel->wherePrimary($activityID);
		$sel->update(array(
			self::COLUMN_VIEWED => 1
		));
	}

	/**
	 * Označí všechny aktivity daného usera za přečtené
	 * @param int $userID ID vlastníka aktivit
	 */
	public function markAllViewed($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_VIEWED, 0);
		$sel->where(self::COLUMN_EVENT_OWNER_ID, $userID);
		$sel->update(array(
			self::COLUMN_VIEWED => 1
		));
	}

}
