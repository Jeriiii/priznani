<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * ImageLikesDao
 * slouží k práci s lajkama obrázků
 *
 * @author Daniel Holubář
 */
class ImageLikesDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "image_likes";

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
	 */
	public function addLiked($imageID, $userID) {
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

}
