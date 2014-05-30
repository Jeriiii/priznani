<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Rozšiřuje základní galerii o zobrazení všech galerií konkrétního uživatele
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent\Galleries\UserImagesInGallery;

use \Nette\Security\User,
	Nette\Utils\Strings;

class UserImagesInGallery extends BaseUserImagesInGallery {

	public function render($mode, $owner) {
		$templateName = "../UserImagesInGallery/userImagesInGallery.latte";

		$this->renderBase($mode, $owner, $templateName);
	}

}

?>
