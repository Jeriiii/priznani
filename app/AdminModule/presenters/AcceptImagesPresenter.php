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

class AcceptImagesPresenter extends AdminSpacePresenter {

	public function renderDefault() {
		$images = $this->context->createUsersImages()->where('allow', 0);
		$this->template->images = $images;

		$this->template->userId = 88;
	}

	public function handleAcceptImage($imgId, $galleryId, $userId) {
		$image = $this->context->createUsersImages()->find($imgId);
		$image->update(array('allow' => 1));
		$this->context->createStream()->aliveGallery($galleryId, $userId);

		//if($this->isAjax("acceptButton")) {}
	}

	public function handleDeleteImage($imgId, $galleryId) {
		if ($this->isAjax("acceptButton")) {

			$imageToDelete = $this->context->createUsersImages()->where('id', $imgId)->fetch();

			unlink(WWW_DIR . "/images/userGalleries/" . $imageToDelete->gallery->userId . "/" . $galleryId . "/" . $imgId . "." . $imageToDelete->suffix);
			unlink(WWW_DIR . "/images/userGalleries/" . $imageToDelete->gallery->userId . "/" . $galleryId . "/" . "galScrn" . $imgId . "." . $imageToDelete->suffix);
			unlink(WWW_DIR . "/images/userGalleries/" . $imageToDelete->gallery->userId . "/" . $galleryId . "/" . "min" . $imgId . "." . $imageToDelete->suffix);
			unlink(WWW_DIR . "/images/userGalleries/" . $imageToDelete->gallery->userId . "/" . $galleryId . "/" . "minSqr" . $imgId . "." . $imageToDelete->suffix);

			$this->context->createUsersImages()->where('id', $imgId)->fetch()->delete();
		}
	}

}
