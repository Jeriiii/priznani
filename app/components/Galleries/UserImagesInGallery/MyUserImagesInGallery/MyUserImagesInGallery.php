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

	/** @var int ID galerie */
	private $galleryID;

	public function __construct($galleryID, $images, \POS\Model\UserDao $userDao) {
		parent::__construct($images, $userDao);
		$this->galleryID = $galleryID;
	}

	/**
	 * vyrendrování
	 * @param type $mode
	 */
	public function render($mode) {
		$templateName = "../MyUserImagesInGallery/myUserImagesInGallery.latte";

		$this->template->galleryID = $this->galleryID;
		$this->renderBase($mode, $this->getUser()->id, $templateName);
	}

}

?>
