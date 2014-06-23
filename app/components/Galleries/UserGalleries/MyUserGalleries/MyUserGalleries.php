<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Rozšiřuje základní galerii o editaci nad obrázkama a vytváření nových obrázků
 *
 * @author Mario
 */

namespace POSComponent\Galleries\UserGalleries;

use \Nette\Security\User,
	Nette\Utils\Strings;

class MyUserGalleries extends BaseUserGalleries {

	/**
	 * vyrendrování
	 * @param type $mode
	 */
	public function render($mode) {
		$userID = $this->getUser()->id;
		$galleries = $this->userGalleryDao->getInUser($userID);

		$templateName = "../MyUserGalleries/myUserGalleries.latte";

		$this->renderBase($mode, $galleries, $userID, $templateName);
	}

}

?>
