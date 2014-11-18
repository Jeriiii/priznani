<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Model\YouAreSexyDao;

/**
 * ImageLikesDao
 * slouží k práci s lajkama obrázků
 *
 * @author Daniel Holubář
 */
class ImageLikesDao extends BaseLikeDao implements ILikeDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "like_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_IMAGE_ID = "imageID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá vazbu mezi obrázkem a uživatelem, který ho lajkl
	 * @param int $imageID ID obrázku, který je lajkován
	 * @param int $userID ID uživatele, který lajkuje
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 */
	public function addLiked($imageID, $userID, $ownerID) {
		/* přidá vazbu mezi obr. a uživatelem */
		$sel = $this->getTable();
		$sel->insert(array(
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_USER_ID => $userID,
		));

		/* zvýší like u obrázku o jedna */
		$sel = $this->createSelection(UserImageDao::TABLE_NAME);
		$image = $sel->get($imageID);
		if (!empty($image)) {
			$image->update(array(
				UserImageDao::COLUMN_LIKES => $image->likes + 1
			));
		}

		/* přidá liknutí do aktivit cílovýho uživatele */
		$this->addActivity($ownerID, $userID, $image->id);

		return $image;
	}

	/**
	 * Zjistí, jestli byl obrázek lajknut uživatelem
	 * @param int $userID ID uživatele, který je přihlášený
	 * @param int $imageID ID opbrázku, který se prohlíží
	 * @return boolean
	 */
	public function likedByUser($userID, $imageID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_IMAGE_ID => $imageID,
		));
		$liked = $sel->fetch();

		if ($liked) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Přidá lajk do aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $imageID ID obrázku.
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function addActivity($ownerID, $creatorID, $imageID) {
		$sel = $this->getActivityTable();
		$type = "like";
		$activity = ActivitiesDao::createImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel);
		return $activity;
	}

	/**
	 * Odstraní lajk z aktivit
	 * @param int $ownerID ID uživatele, kterému obrázek patří.
	 * @param int $creatorID ID uživatele, který obrázek lajknul
	 * @param int $imageID ID obrázku.
	 */
	public function removeActivity($ownerID, $creatorID, $imageID) {
		$sel = $this->getActivityTable();
		$type = "like";
		ActivitiesDao::removeImageActivityStatic($creatorID, $ownerID, $imageID, $type, $sel);
	}

}
