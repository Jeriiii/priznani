<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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

	public function renderDefault() {
		$images = $this->userImageDao->getUnapproved();
		$this->template->images = $images;
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

}
