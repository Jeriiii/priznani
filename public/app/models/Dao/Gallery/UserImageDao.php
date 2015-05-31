<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Context;
use POS\Model\UserGalleryDao;
use POS\Model\StreamDao;
use NetteExt\Arrays;
use Nette\Http\Session;
use Nette\DateTime;

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
	const COLUMN_GAL_SCRN_WIDTH = "widthGalScrn";
	const COLUMN_GAL_SCRN_HEIGHT = "heightGalScrn";
	const COLUMN_CHECK_APPROVED = "checkApproved";
	const COLUMN_CREATED = "created";
	const COLUMN_ON_FRONT_PAGE = 'isOnFrontPage';

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

	public function __construct(Context $database, Session $session, UserGalleryDao $userGalleryDao, StreamDao $streamDao) {
		parent::__construct($database, $session);
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
	 * @param int $loggedUserID ID přihlášeného uživatele
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllFromUser($userID, $loggedUserID, UserGalleryDao $userGalleryDao) {
		/* vybrání všech galeriií uživatele */
		$galls = $userGalleryDao->getInUser($userID);
		$accessData = $userGalleryDao->getAccessData($userID, $loggedUserID, $galls);

		$accessGallIDs = array(); //galerie, které uživatel smí vidět

		foreach ($galls as $gall) {
			if (array_key_exists($gall->id, $accessData) && $accessData[$gall->id] == TRUE) {
				$accessGallIDs[] = $gall->id;
			}
		}

		/* vybrání všech fotek z těchto galerií */
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $accessGallIDs);
		return $sel;
	}

	/**
	 * Vrátí všechny neschválené obrázky, které nejsou ověřovací.
	 * @param boolean $rejected TRUE = Chci jen nevrácené fotky
	 * @return Nette\Database\Table\Selection
	 */
	public function getUnapproved($rejected = FALSE) {
		$sel = $this->getTable();

		$sel->where("." . self::COLUMN_GALLERY_ID . "." . UserGalleryDao::COLUMN_VERIFICATION, 0);
		$sel->where(self::COLUMN_APPROVED, 0);
		if ($rejected) {
			$sel->where(self::COLUMN_REJECTED, 0);
		}
		return $sel;
	}

	/**
	 * Vrátí všechny nezkontrolované obrázky, které nejsou ověřovací.
	 * @return Nette\Database\Table\Selection
	 */
	public function getNotCheck() {
		$sel = $this->getTable();

		$sel->where(":" . UserGalleryDao::TABLE_NAME . "." . UserGalleryDao::COLUMN_VERIFICATION, 0);
		$sel->where(self::COLUMN_CHECK_APPROVED, 1);
		return $sel;
	}

	/**
	 * Vrátí všechny nezkontrolované obrázky, u kterých není rozhodnuto, zda mohou jít na hlavní stranu.
	 * @return Nette\Database\Table\Selection
	 */
	public function getNotCheckFrontPage() {
		$sel = $this->getTable();

		$sel->where(self::COLUMN_ON_FRONT_PAGE . ' IS NULL');
		return $sel;
	}

	/**
	 * Vrátí obrázek, který má uživatel ohodnit
	 * @param int $userId ID uživatele
	 * @return \Nette\Database\Table\ActiveRow Obrázek, který má uživatel ohodnit.
	 */
	public function getFrontPage($userId, $categoryIDs) {
		/* načte obrázky, které již byly ohodnoceny */
		$rateImgIDs = $this->getRateImgIDs($userId);
		$likeImgIDs = $this->getLikeImgIDs($userId);

		$imgIDs = $rateImgIDs + $likeImgIDs;

		$sel = $this->getTable();

		if (!empty($imgIDs)) {
			$sel->where(self::TABLE_NAME . '.' . self::COLUMN_ID . ' NOT ', $imgIDs);
		}
		$sel->where(
			'.' . self::COLUMN_GALLERY_ID . '.' . UserGalleryDao::COLUMN_USER_ID . "."
			. UserDao::COLUMN_PROPERTY_ID . "." . UserPropertyDao::COLUMN_PREFERENCES_ID . " IN"
			, $categoryIDs);
		$sel->where('.' . self::COLUMN_GALLERY_ID . '.' . UserGalleryDao::COLUMN_USER_ID . ' != ?', $userId);
		$sel->where('.' . self::COLUMN_GALLERY_ID . '.' . UserGalleryDao::COLUMN_PRIVATE . ' = ?', 0);
		$sel->where(self::COLUMN_ON_FRONT_PAGE . ' = ?', 1);
		$sel->limit(1);

		return $sel->fetch();
	}

	/**
	 * Vrátí všechny obrázky, které mají jít na hl. stránku.
	 */
	public function getImagesOnFrontPage() {
		$sel = $this->getTable();

		$sel->where(self::COLUMN_ON_FRONT_PAGE . ' = ?', 1);

		return $sel;
	}

	/**
	 * Vrátí všechny ID obrázků, které uživatel ohodnotil.
	 * @param int $userId ID uživatele
	 * @return array Pole obrázků, které uživatel ohodnotil.
	 */
	private function getRateImgIDs($userId) {
		/* vybere všechny obrázky, které již uživatel hodnotil */
		$selRate = $this->createSelection(RateImageDao::TABLE_NAME);
		$selRate->where(RateImageDao::COLUMN_USER_ID, $userId);
		$selRate->select(RateImageDao::COLUMN_IMAGE_ID);

		$ratingImgIDs = array();
		foreach ($selRate as $imgRate) {
			$ratingImgIDs[] = $imgRate->imageID;
		}

		return $ratingImgIDs;
	}

	/**
	 * Vrátí všechny ID obrázků, které uživatel ohodnotil.
	 * @param int $userId ID uživatele
	 * @return array Pole obrázků, které uživatel ohodnotil.
	 */
	private function getLikeImgIDs($userId) {
		/* vybere všechny obrázky, které již uživatel likenul */
		$selLike = $this->createSelection(ImageLikesDao::TABLE_NAME);
		$selLike->where(ImageLikesDao::COLUMN_USER_ID, $userId);
		$selLike->select(ImageLikesDao::COLUMN_IMAGE_ID);

		$likeImgIDs = array();
		foreach ($selLike as $imgLake) {
			$likeImgIDs[] = $imgLake->imageID;
		}

		return $likeImgIDs;
	}

	/**
	 * Spočítá počet obrázků za jeden den.
	 * @param DateTime $day Den, za který se mají statistiky spočítat.
	 * @return int Počet registrací za danný den.
	 */
	public function countByDay(DateTime $day) {
		$sel = parent::getByDay($day, self::COLUMN_CREATED);

		return $sel->count();
	}

	/**
	 * Spočítá počet obrázků za jeden měsíc.
	 * @param DateTime $month Den, za který se mají statistiky spočítat.
	 * @return int Počet registrací za danný den.
	 */
	public function countByMonth(DateTime $month) {
		$sel = parent::getByMonth($month, self::COLUMN_CREATED);

		return $sel->count();
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
	 * Vrátí neověřené obrázky z verifikačních galerií
	 * @return Nette\Database\Table\Selection
	 */
	public function getVerifUnapprovedImages() {
		$sel = $this->getTable();
		$sel->where(":" . UserGalleryDao::TABLE_NAME . "." . UserGalleryDao::COLUMN_VERIFICATION, 1);
		$sel->where(self::TABLE_NAME . "." . self::COLUMN_APPROVED, 0);
		$sel->where(self::TABLE_NAME . "." . self::COLUMN_REJECTED, 0);
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
		$unapprovedCount = $sel->count();


		$notCheckImagesCount = $this->getNotCheck()->count(self::TABLE_NAME . "." . self::COLUMN_ID);
		$notCheckFrontPageImagesCount = $this->getNotCheckFrontPage()->count(self::TABLE_NAME . "." . self::COLUMN_ID);

		return $unapprovedCount + $notCheckImagesCount + $notCheckFrontPageImagesCount;
	}

	/**
	 * Jedná se o profilovou fotku?
	 * @param int $imageId Id obrázku.
	 * @return bool TRUE = jde o profilovou fotku, jinak FALSE.
	 */
	public function isProfile($imageId) {
		/* pokusí se najít uživatele s touto profilovou fotkou */
		$sel = $this->createSelection(UserDao::TABLE_NAME);
		$sel->where(UserDao::COLUMN_PROFIL_PHOTO_ID, $imageId);

		$user = $sel->fetch();
		return $this->exist($user);
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
	 * @param int $checkApproved Fotka se má zkontrolovat 1, nemá 0
	 * @return Database\Table\IRow Nový řádek s obrázekm
	 */
	public function insertImage($name, $suffix, $description, $galleryID, $allow, $checkApproved) {
		$image = $this->insert(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_SUFFIX => $suffix,
			self::COLUMN_DESCRIPTION => $description,
			self::COLUMN_GALLERY_ID => $galleryID,
			self::COLUMN_APPROVED => $allow,
			self::COLUMN_CHECK_APPROVED => $checkApproved
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
			self::COLUMN_CHECK_APPROVED => 0,
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
			self::COLUMN_CHECK_APPROVED => 0,
			self::COLUMN_INTIM => 1
		));

		$image = $sel->fetch();
		$gallery = $image->gallery;

		if ($gallery->intim == 0) { //když není galerie intimní, schávlí ji
			$gallery->update(array(
				UserGalleryDao::COLUMN_INTIM => 1
			));
		}

		return $image;
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
				self::COLUMN_APPROVED => 1,
				self::COLUMN_CHECK_APPROVED => 0,
				self::COLUMN_REJECTED => 0
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
