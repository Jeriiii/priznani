<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserGalleryDao extends BaseGalleryDao {

	const TABLE_NAME = "user_galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_BEST_IMAGE_ID = "bestImageID";
	const COLUMN_LAST_IMAGE_ID = "lastImageID";
	const COLUMN_MAN = "man";
	const COLUMN_WOMEN = "women";
	const COLUMN_COUPLE = "couple";
	const COLUMN_MORE = "more";
	const COLUMN_DEFAULT = "default";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "description";
	const COLUMN_PROFILE = "profil_gallery";
	const COLUMN_VERIFICATION = "verification_gallery";
	const COLUMN_PRIVATE = "private";
	const COLUMN_INTIM = "intim";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí galerie určitého uživatele.
	 * @param int $userID ID uživatele.
	 * @return Nette\Database\Table\Selection
	 */
	public function getInUser($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí galerie určitého uživatele, které nejsou verifikační.
	 * @param type $userID ID uživatele, jehož galerie hledáme
	 * @return Nette\Database\Table\Selection
	 */
	public function getInUserWithoutVerif($userID, $viewerID) {

		$selAllow = $this->createSelection(UserAllowedDao::TABLE_NAME);
		$allowedGalleries = $selAllow->where(UserAllowedDao::COLUMN_USER_ID, $viewerID);
		$allowed = array();

		if (count($allowedGalleries) != 0) {
			foreach ($allowedGalleries as $item) {
				if ($item->gallery->userID == $userID) {
					$allowed[] = $item->galleryID;
				}
			}
		}
		$sel = $this->getTable();
		if (!empty($allowed)) {
			$sel->where(self::COLUMN_USER_ID, $userID)->where(self::COLUMN_VERIFICATION . " = ? OR id IN ?", 0, $allowed);
		} else {
			$sel->where(self::COLUMN_USER_ID, $userID)->where(self::COLUMN_VERIFICATION, 0);
		}

		$sel->order(self::COLUMN_ID . " DESC");

		return $sel;
	}

	/**
	 * Vrátí poslední vytvořenou galerii uživatele
	 * @param int $userID ID uživatele
	 */
	public function findByUser($userID) {
		$sel = $this->getInUser($userID);
		return $sel->fetch();
	}

	/**
	 * Vrátí galerii která má nejlepší NEBO poslední obrázek tento
	 * @param int $bestimageID ID nejlepšího obrázku.
	 * @param int $lastImageID ID posledního obrázku.
	 * @return bool|Database\Table\IRow
	 */
	public function findByBestOrLastImage($bestimageID, $lastImageID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_BEST_IMAGE_ID . " = ? OR " . self::COLUMN_LAST_IMAGE_ID . " = ?", $bestimageID, $lastImageID);
		return $sel->fetch();
	}

	/**
	 * Vrátí defaultní galerii uživatele, když existuje
	 * @param int $userID ID uživatele.
	 * @return bool|Database\Table\IRow
	 */
	public function findDefaultGallery($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_DEFAULT, 1);
		return $sel->fetch();
	}

	/**
	 * Vrátí profilovou galerii uživatele, když existuje
	 * @param int $userID ID uživatele.
	 * @return bool|Database\Table\IRow
	 */
	public function findProfileGallery($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_PROFILE, 1);
		return $sel->fetch();
	}

	/**
	 * Vrátí všechny verifikační galerie
	 * @return Nette\Database\Table\Selection
	 */
	public function findVerificationGalleries() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_VERIFICATION, 1);
		return $sel;
	}

	/**
	 * Vrátí verifiakční galerii určitého uživatele
	 * @param type $userID ID uživatele, jehož verifikační galerii hledáme
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findVerificationGalleryByUser($userID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_ID, $userID);
		$sel->where(self::COLUMN_VERIFICATION, 1);
		return $sel->fetch();
	}

	/**
	 * Vytvoří defaultní galerii uživateli
	 * @param int $userID ID uživatele.
	 * @return Database\Table\IRow
	 */
	public function createDefaultGallery($userID) {
		$sel = $this->getTable();
		$defaultGallery = $sel->insert(array(
			self::COLUMN_NAME => "Moje fotky",
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_DEFAULT => 1,
		));
		return $defaultGallery;
	}

	/**
	 * Vytvoří profilovou galerii uživateli
	 * @param int $userID ID uživatele.
	 * @return Database\Table\IRow
	 */
	public function createProfileGallery($userID) {
		$sel = $this->getTable();
		$profileGallery = $sel->insert(array(
			self::COLUMN_NAME => "Profilové fotky",
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_PROFILE => 1,
		));
		return $profileGallery;
	}

	/**
	 * Vytvoří verifikační galerii určitého uživatele
	 * @param type $userID IDuživatele, pro kterého vytvoříme galerii
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function createVerificationGallery($userID) {
		$sel = $this->getTable();
		$verificationGallery = $sel->insert(array(
			self::COLUMN_NAME => "Ověřovací fotky",
			self::COLUMN_USER_ID => $userID,
			self::COLUMN_VERIFICATION => 1,
		));
		return $verificationGallery;
	}

	/**
	 * Vrátí poslední obrázek z galerie usera
	 * @param int $userID ID usera, jemuž patří galerie
	 * @return @return Database\Table\ActiveRow
	 */
	public function getLastImageByUser($userID) {
		$sel = $this->getTable();
		$sel->select("lastImageID");
		$sel->where(self::COLUMN_USER_ID, $userID);
		return $sel->fetch();
	}

	/**
	 * Získá informace o přistupech do galerií uživatele. Přidá do session, pokud vní nejsou.
	 * @param int $ownerID ID vlastníka galerie
	 * @param int $loggedUserID ID přihlášeného uživatele.
	 * @param Nette\Database\Table\Selection $galleries Galerie u kterých se má ověřit, jestli do ní má uživatel přístup
	 * @return array
	 */
	public function getAccessData($ownerID, $loggedUserID, $galleries) {
		$sectionGalleryAccess = $this->getGallAccessSection();
		$gallAccess = $sectionGalleryAccess->galleriesAccess;

		if (empty($gallAccess)) {
			$gallAccess = $this->getGalleriesAccesInfo($loggedUserID, $ownerID, $gallAccess, $galleries);
		} else {
			foreach ($galleries as $gallery) {
				if (!array_key_exists($gallery->id, $gallAccess)) {
					/* jednorázově doplní data o všech galeriích */
					$gallAccess = $this->getGalleriesAccesInfo($loggedUserID, $ownerID, $gallAccess, $galleries);
					break; //všechna data doplnněna, alg. se může ukončit
				}
			}
		}

		$sectionGalleryAccess->galleriesAccess = $gallAccess;
		return $gallAccess;
	}

	/**
	 * Při procházení galerií cizích lidí ukládá informace o přistupu
	 * přihlášeného uživatele do session. V případě neexistence záznamu
	 * o nějké galerii je informace o ní připojena k dosavadním datům.
	 * @param int $loggedUserID ID přihlášeného uživatele
	 * @param int $ownerID ID vlastníka galerie
	 * @param array $gallAccess Data o přístupu uživatelů k jednotlivým galeriím
	 * @param Nette\Database\Table\Selection $galleries Galerie u kterých se má ověřit, jestli do ní má uživatel přístup
	 * @return array Pole s daty o přístupu uživatele do galerií
	 */
	public function getGalleriesAccesInfo($loggedUserID, $ownerID, $gallAccess, Selection $galleries) {
		$isFriend = $this->checkFriend($loggedUserID, $ownerID);

		$galleriesAccess = empty($gallAccess) ? array() : $gallAccess; //pukud není zatím žádný záznam v session

		foreach ($galleries as $gallery) {
			$galleriesAccess = $this->getGalleryAccessInfo($galleriesAccess, $gallery, $loggedUserID, $isFriend);
		}

		return $galleriesAccess;
	}

	/**
	 * Zjistí, zda má uživatel přístup do této galerie.
	 * @param \Nette\Database\Table\ActiveRow $gallery Galerie u které se má zjistit, zda do ní uživatel může nebo ne.
	 * @param int $loggedUserID ID přihlášeného uživatele
	 * @param int $ownerID ID vlastníka galerie
	 * @return bool Má uživatel přístup do galerie nebo ne.
	 */
	public function haveAccessIntoGallery($gallery, $loggedUserID, $ownerID) {
		$isFriend = $this->checkFriend($loggedUserID, $ownerID);
		$sectionGalleryAccess = $this->getGallAccessSection();
		$gallAccess = $sectionGalleryAccess->galleriesAccess;
		$gallAccess = empty($gallAccess) ? array() : $gallAccess; //pukud není zatím žádný záznam v session

		$galleriesAccess = $this->getGalleryAccessInfo($gallAccess, $gallery, $loggedUserID, $isFriend);
		return $galleriesAccess[$gallery->id];
	}

	/**
	 * Zjistí, zda má uživatel přístup do této galerie.
	 * @param array $galleriesAccess Data o přístupu uživatelů k jednotlivým galeriím, nebo prázdné pole.
	 * @param \Nette\Database\Table\ActiveRow $gallery Galerie u které se má zjistit, zda do ní uživatel může nebo ne.
	 * @param int $loggedUserID ID přihlášeného uživatele
	 * @param bool $isFriend Je uživatel přítel majitele galerie
	 * @return array Data o přístupu uživatelů k jednotlivým galeriím.
	 */
	private function getGalleryAccessInfo(array $galleriesAccess, $gallery, $loggedUserID, $isFriend) {
		/* pokud je uživatel vlastníkem automaticky má přístup (občas se tam objeví id uživatelovo galerie ikdyž ji neprochazim) */
		if ($loggedUserID == $gallery->userID /* owner */) {
			$galleriesAccess[$gallery->id] = TRUE;
			return $galleriesAccess;
		}

		/* pokud záznam ještě není v session */
		if (!array_key_exists($gallery->id, $galleriesAccess)) {
			$galleriesAccess[$gallery->id] = FALSE;

			/* pokud je nastavena privátní galerie, zkoumáme podmínky pro přístup */
			if ($gallery->private) {
				if ($isFriend && $gallery->allow_friends) {// pokud je kamarád a galerie je kamarádům přístupná
					$galleriesAccess[$gallery->id] = TRUE;
				} else if ($this->checkAllowed($gallery->id, $loggedUserID)) { // Zkusí se podívat do DB, zda mu vlastník nedal přístup ručně
					$galleriesAccess[$gallery->id] = TRUE;
				}
			} else { // galerie je veřejná, proto má vstup garantován
				$galleriesAccess[$gallery->id] = TRUE;
			}
		}

		return $galleriesAccess;
	}

	/**
	 * Vrátí sečnu k přístupu do galerií.
	 * @return Sečna k přístupům do galerí.
	 */
	private function getGallAccessSection() {
		return $this->getUnicateSection(self::TABLE_NAME, '2 hours');
	}

	/**
	 * Podle parametrů vyhodnotí, zda je uživateli
	 * dovoleno procházet galerii na základě povolení vlastníka
	 * @param int $galleryID ID galerie
	 * @param int $userID ID uživatele
	 * @return boolean může/nemůže prcházet galerii
	 */
	private function checkAllowed($galleryID, $userID) {
		$selAllowed = $this->createSelection(UserAllowedDao::TABLE_NAME);
		$selAllowed->where(UserAllowedDao::COLUMN_GALLERY_ID, $galleryID);
		$selAllowed->where(UserAllowedDao::COLUMN_USER_ID, $userID);
		$allowed = $selAllowed->fetch();
		if ($allowed) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Zjistí, zda je uživatel přítelem vlastníka
	 * galerie
	 * @param int $userID ID uživatele
	 * @param int $ownerID ID vlastníka
	 * @return boolean je/není přítel
	 */
	private function checkFriend($userID, $ownerID) {
		if ($userID == $ownerID) {
			return TRUE;
		}
		$selFriend = $this->createSelection(FriendDao::TABLE_NAME);
//zjistím, jestli se jedná o kamaráda
		$selFriend->where(FriendDao::COLUMN_USER_ID_1, $userID);
		$selFriend->where(FriendDao::COLUMN_USER_ID_2, $ownerID);
		$isFriend = $selFriend->fetch();
		if ($isFriend) {
			return TRUE;
		}
		return FALSE;
	}

	/*	 * ****************************** UPDATE **************************** */

	/**
	 * Změní nejlepší a poslední vložený obrázek.
	 * @param int $galleryID ID galerie
	 * @param type $bestImageID ID nejlepšího obrázku.
	 * @param type $lastImageID ID posledního vloženého obrázku.
	 */
	public function updateBestAndLastImage($galleryID, $bestImageID, $lastImageID) {
		$sel = $this->getTable();
		$sel->wherePrimary($galleryID);
		$sel->update(array(
			UserGalleryDao::COLUMN_BEST_IMAGE_ID => $bestImageID,
			UserGalleryDao::COLUMN_LAST_IMAGE_ID => $lastImageID
		));
	}

	/**
	 * Změní hodnoty muž|žena|pár|3 a více u galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $man Muž.
	 * @param int $women Žena.
	 * @param int $couple Par.
	 * @param int $more 3 a více.
	 */
	public function updateGender($galleryID, $man, $women, $couple, $more) {
		$sel = $this->getTable();
		$sel->wherePrimary($galleryID);
		$sel->update(array(
			self::COLUMN_MAN => $man,
			self::COLUMN_WOMEN => $women,
			self::COLUMN_COUPLE => $couple,
			self::COLUMN_MORE => $more
		));
	}

	/**
	 * PRO CRON
	 * Přepočítá všechny galerie, jestli se v nich nenachází intimní fotka.
	 */
	public function recalIntims() {
		$sel = $this->getTable();

		foreach ($sel as $gallery) {
			$images = $this->createSelection(UserImageDao::TABLE_NAME);
			$images->where(UserImageDao::COLUMN_GALLERY_ID, $gallery->id);
			$images->where(UserImageDao::COLUMN_INTIM, 1);

			if ($images->fetch()) { //pokud je v ní alespoň jeden intimní obrázek
				$gallery->update(array(
					self::COLUMN_INTIM => 1
				));
			}
		}
	}

}
