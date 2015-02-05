<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of AcceptImagesPresenter
 *
 * @author Daniel Holubář
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use NetteExt\File;
use NetteExt\Path\GalleryPathCreator;

class AcceptImagesPresenter extends AdminSpacePresenter {

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\ActivitiesDao @inject */
	public $ActivitiesDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGalleryDao;

	/** @var \POS\Model\CompetitionsImagesDao @inject */
	public $competitionsImagesDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var Nette\Database\Table\Selection Uživatelské obrázky */
	public $userImages;

	/** @var Nette\Database\Table\Selection Obrázky ze soutěží. */
	public $compImages;

	/** @var Nette\Database\Table\Selection Ověřovací obrázky. */
	public $verificationImages;

	/** @var Nette\Database\Table\Selection Neykontrolované automaticky schválené obrázky. */
	public $userNotCheckImages;

	/** Načte neschválené obrázky z DB */
	private function setImages() {
		$this->verificationImages = $this->userImageDao->getVerifUnapprovedImages();
		$this->compImages = $this->competitionsImagesDao->getUnapproved();
		$this->userImages = $this->userImageDao->getUnapproved();
		$this->userNotCheckImages = $this->userImageDao->getNotCheck();
	}

	/**
	 * Nastaví countery do templaty
	 */
	private function setCounters() {
		$this->template->usrCount = $this->userImages->count(UserImageDao::TABLE_NAME . ".id");
		$this->template->compCount = $this->compImages->count("id");
		$this->template->verCount = $this->verificationImages->count(UserImageDao::TABLE_NAME . ".id");
		$this->template->notCheckCount = $this->userNotCheckImages->count(UserImageDao::TABLE_NAME . ".id");
	}

	public function renderDefault() {
		$this->setImages();
		$this->setCounters();

		$this->template->images = $this->userImages;
	}

	public function renderAcceptCompetitionImages() {
		$this->setImages();
		$this->setCounters();

		$this->template->images = $this->compImages;
	}

	public function renderAcceptVerificationImages() {
		$this->setImages();
		$this->setCounters();

		$this->template->images = $this->verificationImages;
	}

	public function renderCheckUserImages() {
		$this->setImages();
		$this->setCounters();

		$this->template->images = $this->userNotCheckImages;
	}

	/**
	 * Schvaluje uživatelské a ověřovací obrázky
	 * @param type $imgId ID obrázku
	 * @param type $galleryId ID galerie
	 * @param type $userID ID vlastníka obrázku
	 */
	public function handleAcceptImage($imgId, $galleryId, $userID) {
		$image = $this->userImageDao->approve($imgId);
		if ($image->gallery->verification_gallery) {
			$this->userDao->verify($userID);
			$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "verification");
		} else {
			$user = $this->userDao->find($userID);
			$this->streamDao->aliveGallery($galleryId, $userID, $user->property->preferencesID);
			$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "approve");
		}

		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->invalidateControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * Odmítne oběřovací foto
	 * @param type $imgID ID obrázku
	 */
	public function handleRejectImage($imgID) {
		$image = $this->userImageDao->reject($imgID);
		$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $image->gallery->userID, $imgID, "reject");

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * Maže uživatelské a ověřovací obrázky
	 * @param type $imgId ID obrázku, který bude smazán
	 */
	public function handleDeleteImage($imgId) {
		$image = $this->userImageDao->find($imgId);
		$userID = $image->gallery->userID;

		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($image->galleryID, $userID);
		File::removeImage($image->id, $image->suffix, $galleryFolder);

		$image->delete();
		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->invalidateControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * Schvaluej obrázky ze soutěže
	 * @param type $imageID ID obrázku, který bude schválen
	 */
	public function handleAcceptCompetitionImage($imageID) {
		$comImage = $this->competitionsImagesDao->acceptImage($imageID);
		$user = $comImage->image->gallery->user;
		$this->streamDao->aliveGallery($comImage->image->galleryID, $user->id, $user->property->preferencesID);
		$this->invalidateMenuData();

		$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $comImage->image->gallery->userID, $comImage->imageID, "approve");

		if ($this->isAjax()) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * Maže obrázky ze soutěže
	 * @param type $imageID ID obrázku, který bude samzán
	 */
	public function handleDeleteCompetitionImage($imageID) {
		$image = $this->competitionsImagesDao->find($imageID);
		$userID = $image->gallery->userID;

		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($image->galleryID, $userID);
		File::removeImage($image->id, $image->suffix, $galleryFolder);

		$image->delete();
		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * schválí fotku jako intimní
	 * @param type $imgId ID obrázku
	 * @param type $galleryId ID galerie
	 * @param type $userID ID uživatele, kterému patří obrázek
	 */
	public function handleAcceptIntimImage($imgId, $galleryId, $userID) {

		$image = $this->userImageDao->approveIntim($imgId);
		if ($image->gallery->verification_gallery) {
			$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "verification");
		} else {
			$user = $this->userDao->find($userID);
			$this->streamDao->aliveGallery($galleryId, $userID, $user->property->preferencesID);
			$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "approve");
		}

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * schválí fotku jako intimní
	 * @param type $imgId ID obrázku
	 */
	public function handleAcceptIntimComImage($imgId) {

		$comImage = $this->competitionsImagesDao->acceptImageIntim($imgId);
		$user = $comImage->image->gallery->user;
		$this->streamDao->aliveGallery($comImage->image->galleryID, $user->id, $user->property->preferencesID);
		$this->invalidateMenuData();

		$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $comImage->image->gallery->userID, $comImage->imageID, "approve");

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

}
