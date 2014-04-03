<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Rozšiřuje základní galerii o editaci nad obrázkama a vytváření nových obrázků
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\Galleries\UserImagesInGallery;

use \Nette\Security\User,
	Nette\Utils\Strings;

class MyUserImagesInGallery extends BaseUserImagesInGallery {

	public function __construct($galleryID, $images) {
		parent::__construct($galleryID, $images);
	}
	
	/**
	 * vyrendrování
	 * @param type $mode
	 */
	public function render($mode) {		
		$templateName = "../MyUserImagesInGallery/myUserImagesInGallery.latte";
		
		$this->renderBase($mode, $templateName);
	}

}

?>
 