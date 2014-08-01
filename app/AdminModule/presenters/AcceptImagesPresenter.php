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
		$this->template->compCount = count($compImages);
	}

	public function renderAcceptCompetitionImages() {
		$images = $this->competitionsImagesDao->getUnapproved();
		$compIndexes = $this->getImagesIndexes($images);
		$usrImages = $this->userImageDao->getUnapproved($compIndexes);
		$this->template->images = $images;
		$this->template->usrCount = count($usrImages);
	}

	public function handleAcceptImage($imgId, $galleryId) {
		$image = $this->userImageDao->find($imgId);
		$image->update(array('allow' => 1));

		$userID = $this->userGalleryDao->find($image->galleryID)->userID;

		$this->streamDao->aliveGallery($galleryId, $userID);

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

		if ($this->isAjax("imageAcceptance")) {
			$this->invalidateControl('imageAcceptance');
		} else {
			$this->redirect("this");
		}
	}

	public function handleAcceptCompetitionImage($imageID, $usrImgID) {
		$this->competitionsImagesDao->acceptImage($imageID);
		$usrImage = $this->userImageDao->find($usrImgID);
		$usrImage->update(array('allow' => 1));

		if ($this->isAjax()) {
			$this->redrawControl('imageAcceptance');
		} else {
			$this->redirect('this');
		}
	}

	public function handleDeleteCompetitionImage($imageID) {
		$this->competitionsImagesDao->delete($imageID);

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
