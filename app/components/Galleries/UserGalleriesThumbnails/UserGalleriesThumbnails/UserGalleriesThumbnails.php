<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Rozšiřuje základní galerii o zobrazení všech galerií konkrétního uživatele
 *
 * @author Mario
 */

namespace POSComponent\Galleries\UserGalleriesThumbnails;

use \Nette\Security\User,
	Nette\Utils\Strings;

class UserGalleriesThumbnails extends BaseUserGalleriesThumbnails {

	/**
	 * @param type $mode rozhoduje, zda se mají vygenerovat všechny obrázky v galerii nebo jen pár obrázků
	 * @param type $userID ID uživatele, kterého se mají galerie zobrazit
	 */
	public function render($mode, $userID) {
		//vememe pouze galerie, tkeré nejsou verifikační
		$galleries = $this->userGalleryDao->getInUserWithoutVerif($userID);

		$templateName = "../UserGalleries/userGalleriesThumbnails.latte";

		$this->renderBase($mode, $galleries, $userID, $templateName);
	}

}

?>
