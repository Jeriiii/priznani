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

class AcceptImagesPresenter extends AdminSpacePresenter{
	
	public function renderDefault() {
		$images = $this->context->createUsersImages()->where('allow', 0);
		$this->template->images = $images;
		
		$this->template->userId = 88;
	}
	
	public function handleAcceptImage($imgId, $galleryId, $userId) {
		$gallery = $this->context->createUsersImages()->where('galleryID', $galleryId);
		$galleryCheck = FALSE;
		
		foreach($gallery as $image) {
			if($image->allow == 1) {
				$galleryCheck = TRUE;
			}
		}
		
		if($galleryCheck) {
			$image = $this->context->createUsersImages()->where('id', $imgId)->fetch();
			$image->update(array('allow' => 1));
			$this->context->createStream()->aliveGallery($galleryId, $userId);
			$this->flashMessage("Obrázek schválen.",'success');
		} else {
			$image = $this->context->createUsersImages()->where('id', $imgId)->fetch();
			$image->update(array('allow' => 1));
			$this->context->createStream()->addNewGallery($galleryId, $userId);
			$this->flashMessage("Obrázek schválen.",'success');
		}
		
	}
}
