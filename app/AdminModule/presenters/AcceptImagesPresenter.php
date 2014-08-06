<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of AcceptImagesPresenter
 *
 * @author Daniel
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
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\CompetitionsImagesDao
	 * @inject
	 */
	public $competitionsImagesDao;

	public function renderDefault() {
		$compImages = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($compImages);

		$images = $this->userImageDao->getUnapproved($compIndexes);
		$this->template->images = $images;

		$this->template->usrCount = $images->count("id");
		$this->template->compCount = $compImages->count("id");
	}

	public function renderAcceptCompetitionImages() {
		$images = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($images);
		$usrImages = $this->userImageDao->getUnapproved($compIndexes);
		$this->template->images = $images;

		$this->template->compCount = $images->count("id");
		$this->template->usrCount = $usrImages->count("id");
	}

	public function handleAcceptImage($imgId, $galleryId) {
		$image = $this->userImageDao->find($imgId);
		$image->update(array('allow' => 1));

		$userID = $this->userGalleryDao->find($image->galleryID)->userID;

		$this->streamDao->aliveGallery($galleryId, $userID);
		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->invalidateControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	public function handleDeleteImage($imgId, $galleryId) {
		$image = $this->userImageDao->find($imgId);
		$userID = $image->gallery->userID;

		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryId, $userID);
		File::removeImage($image->id, $image->suffix, $galleryFolder);

		$image->delete();
		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->invalidateControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	public function handleAcceptCompetitionImage($imageID) {
		$this->competitionsImagesDao->acceptImage($imageID);
		$this->invalidateMenuData();

		if ($this->isAjax()) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect('this');
		}
	}

	public function handleDeleteCompetitionImage($imageID) {
		$this->competitionsImagesDao->delete($imageID);
		$this->invalidateMenuData();

		if ($this->isAjax("imageAcceptance")) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	public function getImagesIndexes($images) {
		$indexes = array();

		foreach ($images as $item) {
			$indexes[] = $item->image->id;
		}

		return $indexes;
	}

}
