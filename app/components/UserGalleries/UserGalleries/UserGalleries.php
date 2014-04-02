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

namespace POSComponent\Galleries\UserGalleries;

use \Nette\Security\User,
	Nette\Utils\Strings;

class UserGalleries extends BaseUserGalleries {
	
	/**
	 * @param type $mode rozhoduje, zda se mají vygenerovat všechny obrázky v galerii nebo jen pár obrázků
	 * @param type $userID ID uživatele, kterého se mají galerie zobrazit
	 */
	
	public function render($mode, $userID) {		
		$galleries = $this->getUserGalleries()
						->where("userID", $userID)
						->order('id DESC');
		
		$templateName = "../UserGalleries/userGalleries.latte";
		
		$this->renderBase($mode, $galleries, $templateName);
	}

}

?>
 