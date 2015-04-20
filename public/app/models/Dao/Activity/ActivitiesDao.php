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
	const COLUMN_EVENT_TYPE = "type";
	const COLUMN_IMAGE_ID = "imageID";
	const COLUMN_STATUS_ID = "statusID";
	const COLUMN_EVENT_OWNER_ID = "event_ownerID";
	const COLUMN_EVENT_CREATOR_ID = "event_creatorID";
	const COLUMN_COMMENT_IMAGE_ID = "commentImageID";
	const COLUMN_FRIEND_REQUEST_ID = "friendRequestID";
	const COLUMN_VIEWED = "viewed";
	const COLUMN_SEND_NOTIFY = "sendNotify";

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
		return self::createImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel);
	}

	/**
	 * Odstraní aktivitu pro obrázek.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího obrázek
	 * @param int $imageID ID obrázku
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function removeImageActivity($creatorID, $ownerID, $imageID, $type) {
		$sel = $this->getTable();
		self::removeImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel);
	}

	/**
	 * Přidá aktivitu pro status comment.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího obrázek
	 * @param int $commentID ID obrázku
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createStatusCommentActivity($creatorID, $ownerID, $commentID, $type) {
		$sel = $this->getTable();
		return self::createStatusCommentActivityStatic($creatorID, $ownerID, $imageID, $type, $sel);
	}

	/**
	 * Přidá aktivitu pro obrázek.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího obrázek
	 * @param int $imageID ID obrázku
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function createImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel) {
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Odstraní aktivitu pro obrázek.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího obrázek
	 * @param int $imageID ID obrázku
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function removeImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel) {
		$activity = $sel->where(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		$activity->delete();
	}

	/**
	 * Přidá aktivitu o žádosti o přátelství.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího status
	 * @param int $friendRequestID ID žádosti o přátelství
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createFriendRequestActivity($creatorID, $ownerID, $friendRequestID, $type = "new-request") {
		$sel = $this->getTable();
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_FRIEND_REQUEST_ID => $friendRequestID,
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
		return self::createStatusActivityStatic($creatorID, $ownerID, $statusID, $type, $sel);
	}

	/**
	 * Přidá aktivitu pro status.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího status
	 * @param int $statusID ID statusu
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function createStatusActivityStatic($creatorID, $ownerID, $statusID, $type, $sel) {
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Odstraní aktivitu pro status.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího status
	 * @param int $statusID ID statusu
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function removeStatusActivity($creatorID, $ownerID, $statusID, $type) {
		$sel = $this->getTable();
		self::removeStatusActivityStatic($creatorID, $ownerID, $statusID, $type, $sel);
	}

	/**
	 * Odstraní aktivitu pro status.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele vlastnícího status
	 * @param int $statusID ID statusu
	 * @param string $type Typ aktivity (like, comment, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function removeStatusActivityStatic($creatorID, $ownerID, $statusID, $type, $sel) {
		$activity = $sel->where(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_STATUS_ID => $statusID,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		$activity->delete();
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
		return self::createUserActivityStatic($creatorID, $ownerID, $type, $sel);
	}

	/**
	 * Přidá aktivitu pro uživatele.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function createUserActivityStatic($creatorID, $ownerID, $type, $sel) {
		$activity = $sel->insert(array(
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Přidá aktivitu pro komentář.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param int $commentID ID komentáře
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function createCommentActivity($creatorID, $ownerID, $commentID, $type) {
		$sel = $this->getTable();
		return self::createCommentActivityStatic($creatorID, $ownerID, $commentID, $type, $sel);
	}

	/**
	 * Přidá aktivitu pro komentář.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param int $commentID ID komentáře
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function createCommentActivityStatic($creatorID, $ownerID, $commentID, $type, $sel) {
		$activity = $sel->insert(array(
			self::COLUMN_COMMENT_IMAGE_ID => $commentID,
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity;
	}

	/**
	 * Odstraní aktivitu pro komentář.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param int $commentID ID komentáře
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @return Nette\Database\Table\Selection
	 */
	public function removeCommentActivity($creatorID, $ownerID, $commentID, $type) {
		$sel = $this->getTable();
		self::removeCommentActivityStatic($creatorID, $ownerID, $commentID, $type, $sel);
	}

	/**
	 * Odstraní aktivitu pro komentář.
	 * @param int $creatorID ID uživatele, který aktivitu provádí
	 * @param int $ownerID ID uživatele, kterého se aktivita týká
	 * @param int $commentID ID komentáře
	 * @param string $type Typ aktivity (změna infa o sobě, šťouch, ...)
	 * @param Nette\Database\Table\Selection $sel Čistá tabulka aktivity.
	 * @return Nette\Database\Table\Selection
	 */
	public static function removeCommentActivityStatic($creatorID, $ownerID, $commentID, $type, $sel) {
		$activity = $sel->where(array(
			self::COLUMN_COMMENT_IMAGE_ID => $commentID,
			self::COLUMN_EVENT_TYPE => $type,
			self::COLUMN_EVENT_OWNER_ID => $ownerID,
			self::COLUMN_EVENT_CREATOR_ID => $creatorID
		));
		return $activity->delete();
	}

	/**
	 * Vrátí aktivity pro jednoho usera.
	 * @param int $userID
	 * @param int $limit
	 * @param int $offset
	 * @return Nette\Database\Table\Selection
	 */
	public function getByUserId($userID, $limit = 0, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_EVENT_OWNER_ID, $userID);
		if ($limit != 0) {
			$sel->limit($limit, $offset);
		}
		return $sel->order(self::COLUMN_ID . ' DESC');
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

	/**
	 * Vrátí nepřečtené aktivity o kterých ještě neodešel email s upozorněním.
	 * @return Nepřečtené aktivity o kterých ještě neodešel email s upozorněním.
	 */
	public function getNotViewedNotSendNotify() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEND_NOTIFY, 0);
		$sel->where(self::COLUMN_VIEWED, 0);

		/* Vybere pouze uživatele, kteří se nepřihlásili déle jak den */
		$now = new \Nette\DateTime();
		$now->modify('- 2 day');
		$sel->where(self::COLUMN_EVENT_OWNER_ID . '.last_active < ?', $now);

		$sel = $this->getAfterPeriod($sel);

		return $sel;
	}

	/**
	 * Vrátí aktivity podle toho, jak často o nich uživatel chce posílat oznámení. Pokud je chce posílat
	 * denně, nebo týdně. Pokud týdně, zkontroluje jestli se mu poslední zprávy poslala
	 * před týdnem.
	 * @param \Nette\Database\Table\Selection $activities Aktivity k profiltorvání.
	 * @return \Nette\Database\Table\Selection Profiltrované zprávy.
	 */
	private function getAfterPeriod($activities) {
		/* Vybere uživatele, kteří si přejí zasílat denně. */
		$emailPeriodDaily = self::COLUMN_EVENT_OWNER_ID . '.' . UserDao::COLUMN_EMAIL_NEWS_PERIOD . ' = ?';

		/* Vybere uživatele, kteří si přejí zasílat týdně. */
		$emailPeriodWeekly = self::COLUMN_EVENT_OWNER_ID . '.' . UserDao::COLUMN_EMAIL_NEWS_PERIOD . ' = ?';
		$lastWeekSended = self::COLUMN_EVENT_OWNER_ID . '.' . UserDao::COLUMN_EMAIL_NEWS_LAST_SENDED . ' <= ? ';
		$date = new \Nette\DateTime();
		$date->modify('- 7 day');

		$activities->where('(' . $emailPeriodDaily . ' OR (' . $emailPeriodWeekly . ' AND ' . $lastWeekSended . '))', UserDao::EMAIL_PERIOD_DAILY, UserDao::EMAIL_PERIOD_WEEKLY, $date);

		return $activities;
	}

	/**
	 * Označí tyto aktivity jako aktivity s odeslaným oznámením emailem.
	 */
	public function updateSendNotify($activities) {
		$activities->update(array(
			self::COLUMN_SEND_NOTIFY => 1
		));
	}

}
