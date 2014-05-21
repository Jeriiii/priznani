<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserImageDao extends AbstractDao {

	const TABLE_NAME = "user_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_SUFFIX = "suffix";
	const COLUMN_DESCRIPTION = "description";
	const COLUMN_GALLERY_ID = "galleryID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí obrázky v galerii a seřadí je sestupně podle ID.
	 * @param int $galleryID ID galerie.
	 * @return Nette\Database\Table\Selection
	 */
	public function getInGallery($galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Smaže obrázek, když jde o polední obrázek nebo nejlepší obrázek,
	 * zkusí nastavit jiný. Když už žádný jiný obrázek v galerii není
	 * vymaže ji ze streamu.
	 * @param int $imageID ID obrázku.
	 */
	public function delete($imageID) {
		/* galerie, kterou tento obrázek zastupuje */
		$userGallery = $this->createSelection(UserGalleryDao::TABLE_NAME);
		$userGallery->findByBestOrLastImage($imageID, $imageID);

		/* kontrola, zda se nemaze obrazek zastupujici galerii */
		if ($userGallery) {
			$image = $this->findByGalleryAndNotImageID($imageID, $userGallery->id);

			/* existuji jine obrazky v galerii? */
			if ($image) {
				/* ANO - nastav jiný obrázek */
				$selUserGallery = $this->createSelection(UserGalleryDao::TABLE_NAME);
				$selUserGallery->updateBestAndLastImage($image->id, $image->id);
			} else {
				/* NE - smaž galerii ze streamu */
				$selStream = $this->createSelection(StreamDao::TABLE_NAME);
				$selStream->deleteUserGallery($userGallery->id);
			}
		}

		parent::delete($imageID);
	}

	/**
	 * Vrátí obrázek danné galerie, který nemá toto ID obrázku.
	 * @param int $imageID ID obrázku.
	 * @param int $userGalleryID ID galerie.
	 * @return bool|Database\Table\IRow
	 */
	public function findByGalleryAndNotImageID($imageID, $userGalleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $userGalleryID);
		$sel->where(self::COLUMN_ID . " != ?", $imageID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel->fetch();
	}

	/**
	 * Vloží nový obrázek do databáze
	 * @param string $name Název obrázku.
	 * @param string $suffix Koncovka obrázku.
	 * @param string $description Popisek obrázku.
	 * @param int $galleryID ID galerie.
	 * @return Database\Table\IRow Nový řádek s obrázekm
	 */
	public function insertImage($name, $suffix, $description, $galleryID) {
		$image = $this->insert(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_SUFFIX => $suffix,
			self::COLUMN_DESCRIPTION => $description,
			self::COLUMN_GALLERY_ID => $galleryID
		));

		return $image;
	}

}
