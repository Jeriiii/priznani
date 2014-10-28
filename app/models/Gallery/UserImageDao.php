<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Context;
use POS\Model\UserGalleryDao;
use POS\Model\StreamDao;
use NetteExt\Arrays;

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
	const COLUMN_APPROVED = "approved";
	const COLUMN_LIKES = "likes";
	const COLUMN_COMMENTS = "comments";
	const COLUMN_INTIM = "intim";
	const COLUMN_REJECTED = "rejected";

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function __construct(Context $database, UserGalleryDao $userGalleryDao, StreamDao $streamDao) {
		parent::__construct($database);
		$this->userGalleryDao = $userGalleryDao;
		$this->streamDao = $streamDao;
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
	 * Vybere úplně všechny fotky daného uživatele ze všech galerií
	 * @param int $userID ID uživatele.
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllFromUser($userID) {
		/* vybrání všech galeriií uživatele */
		$galls = $this->createSelection(UserGalleryDao::TABLE_NAME);
		$galls->where(UserGalleryDao::COLUMN_USER_ID, $userID);

		/* vybrání všech fotek z těchto galerií */
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $galls);
		return $sel;
	}

	/**
	 * Vrátí všechny neschválené obrázky.
	 * @return Nette\Database\Table\Selection
	 */
	public function getUnapproved($indexes, $rejected = FALSE) {
		$sel = $this->getTable();
		$sel->where('id NOT', $indexes);
		$sel->where(self::COLUMN_APPROVED, 0);
		if ($rejected) {
			$sel->where(self::COLUMN_REJECTED, 0);
		}
		return $sel;
	}

	/**
	 * Vrátí neověřené obrázky z galerie
	 * @param type $galleryID ID galerie, v které hledáme
	 * @return Nette\Database\Table\Selection
	 */
	public function getUnapprovedImagesInGallery($galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		$sel->where(self::COLUMN_APPROVED, 0);
		$sel->where(self::COLUMN_REJECTED, 0);
		return $sel;
	}

	/**
	 * Vrátí počet nevyřízených fotek
	 * @return int
	 */
	public function getUnapprovedCount() {
		$sel = $this->getTable();
		$sel->select('id');
		$sel->where(self::COLUMN_APPROVED, 0);
		return $sel->count();
	}

	/**
	 * Smaže obrázek, když jde o polední obrázek nebo nejlepší obrázek,
	 * zkusí nastavit jiný. Když už žádný jiný obrázek v galerii není
	 * vymaže ji ze streamu.
	 * @param int $imageID ID obrázku.
	 */
	public function delete($imageID) {
		/* galerie, kterou tento obrázek zastupuje */
		$userGallery = $this->userGalleryDao->findByBestOrLastImage($imageID, $imageID);

		/* kontrola, zda se nemaze obrazek zastupujici galerii */
		if ($userGallery) {
			$image = $this->findByGalleryAndNotImageID($imageID, $userGallery->id);

			/* existuji jine obrazky v galerii? */
			if ($image) {
				/* ANO - nastav jiný obrázek */
				$this->userGalleryDao->updateBestAndLastImage($image->galleryID, $image->id, $image->id);
			} else {
				/* NE - smaž galerii ze streamu */
				$this->streamDao->deleteUserGallery($userGallery->id);
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
	 * @param int $allow Fotka je schválená 1, není 0
	 * @return Database\Table\IRow Nový řádek s obrázekm
	 */
	public function insertImage($name, $suffix, $description, $galleryID, $allow) {
		$image = $this->insert(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_SUFFIX => $suffix,
			self::COLUMN_DESCRIPTION => $description,
			self::COLUMN_GALLERY_ID => $galleryID,
			self::COLUMN_APPROVED => $allow
		));

		return $image;
	}

	/**
	 * Vrátí počet již schválených obrázků danného uživatele.
	 * @param int $userID ID uživatele.
	 * @return int Počet již schválených fotek.
	 */
	public function countAllowedImages($userID) {
		/* Vrátí galerie určitého uživatele. */
		$galls = $this->createSelection(UserGalleryDao::TABLE_NAME);
		$galls->where(UserGalleryDao::COLUMN_USER_ID, $userID);

		$sel = $this->getTable();
		$sel->where(self::COLUMN_APPROVED, 1);
		$sel->where(self::COLUMN_GALLERY_ID, $galls);
		return $sel->count();
	}

	/**
	 * Vrátí obrázky podle pole ID
	 * @param array Pole indexů obrázků
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllById($imagesID) {
		$sel = $this->getTable();
		return $sel->where('id', $imagesID);
	}

	/*	 * **************************** UPDATE ******************************** */

	/**
	 * Schválí fotku.
	 * @param int $id Image ID.
	 */
	public function approve($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_APPROVED => 1,
			self::COLUMN_REJECTED => 0
		));

		return $sel->fetch();
	}

	/**
	 * zamítne fotku.
	 * @param int $id Image ID.
	 */
	public function reject($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_REJECTED => 1,
		));

		return $sel->fetch();
	}

	/**
	 * Schválí intim fotku.
	 * @param int $id Image ID.
	 */
	public function approveIntim($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_APPROVED => 1,
			self::COLUMN_INTIM => 1
		));

		return $sel->fetch();
	}

	/**
	 * Schválí poslední přidanouta zatím neschválenou fotku.
	 * Používá se pro testování.
	 */
	public function approveLast() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_APPROVED, 0);
		$sel->order(self::COLUMN_ID . " DESC");
		$image = $sel->fetch();
		if (!empty($image)) {
			$image->update(array(
				self::COLUMN_APPROVED => 1
			));
		}

		return $image;
	}

	/**
	 *
	 * @param int $imageID
	 * @param string $name
	 * @param string $descrition
	 */
	public function updateImage($imageID, $name, $descrition) {
		$data = Arrays::addVal(self::COLUMN_NAME, $name);
		$data = Arrays::addVal(self::COLUMN_DESCRIPTION, $descrition, $data);

		parent::update($imageID, $data);
	}

}
