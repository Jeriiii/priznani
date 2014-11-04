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

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\ActivitiesDao
	 * @inject
	 */
	public $ActivitiesDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	public function renderDefault() {
		$compImages = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($compImages);

		$verificationData = $this->getVerificationImagesAndIndexes();

		$indexes = array_merge($verificationData[0], $compIndexes);

		$images = $this->userImageDao->getUnapproved($indexes);
		$this->template->images = $images;

		$this->template->usrCount = $images->count("id");
		$this->template->compCount = $compImages->count("id");
		$this->template->verCount = count($verificationData[0]);
	}

	public function renderAcceptCompetitionImages() {
		$images = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($images);

		$verificationData = $this->getVerificationImagesAndIndexes();

		$indexes = array_merge($verificationData[0], $compIndexes);

		$usrImages = $this->userImageDao->getUnapproved($indexes);
		$this->template->images = $images;

		$this->template->compCount = $images->count("id");
		$this->template->usrCount = $usrImages->count("id");
		$this->template->verCount = count($verificationData[1]);
	}

	public function renderAcceptVerificationImages() {
		$images = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($images);

		$verificationData = $this->getVerificationImagesAndIndexes();

		$indexes = array_merge($verificationData[0], $compIndexes);

		$usrImages = $this->userImageDao->getUnapproved($indexes, TRUE);
		$this->template->images = $verificationData[1];

		$this->template->compCount = $images->count("id");
		$this->template->usrCount = $usrImages->count("id");
		$this->template->verCount = count($verificationData[0]);
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
			$this->streamDao->aliveGallery($galleryId, $userID);
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
		$this->streamDao->aliveGallery($comImage->image->galleryID, $comImage->image->gallery->userID);
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
	 * @param type $type označení zda jde o uživatelskou(0) nebo soutěžní(1) fotku
	 */
	public function handleAcceptIntimImage($imgId, $galleryId, $userID, $type) {

		switch ($type) {
			case "0":
				$image = $this->userImageDao->approveIntim($imgId);
				if ($image->gallery->verification_gallery) {
					$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "verification");
				} else {
					$this->streamDao->aliveGallery($galleryId, $userID);
					$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $userID, $imgId, "approve");
				}
				break;
			case "1":
				$comImage = $this->competitionsImagesDao->acceptImageIntim($imgId);
				$this->streamDao->aliveGallery($comImage->image->galleryID, $comImage->image->gallery->userID);
				$this->invalidateMenuData();

				$this->ActivitiesDao->createImageActivity($this->getUser()->getId(), $comImage->image->gallery->userID, $comImage->imageID, "approve");
				break;
			default:
				break;
		}

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	/**
	 * získá indexy obrázků
	 * @param \Nette\Database\Table\Selection $images
	 * @return array
	 */
	private function getImagesIndexes($images) {
		$indexes = array();

		foreach ($images as $item) {
			$indexes[] = $item->image->id;
		}

		return $indexes;
	}

	/**
	 * uloží zvlášť obrázky a zvlášť indexy obrázků.
	 * @return array
	 */
	private function getVerificationImagesAndIndexes() {
		$verGalleries = $this->userGalleryDao->findVerificationGalleries();
		$verIndexes = array();
		$verImages = array();

		foreach ($verGalleries as $item) {
			$verImagesRaw = $this->userImageDao->getUnapprovedImagesInGallery($item->id);
			foreach ($verImagesRaw as $item) {
				$verImages[] = $item;
				$verIndexes[] = $item->id;
			}
		}
		return array(
			0 => $verIndexes,
			1 => $verImages,
		);
	}

}
