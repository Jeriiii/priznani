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
		$this->template->galleries = $this->context->createUsersGalleries();
		$this->template->images = $this->context->createUsersImages();
	}
}
