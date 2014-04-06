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

	public function __construct() {
	}
	
	/**
	 * vyrendrování
	 * @param type $mode
	 */
	public function render($mode) {		
		$galleries = $this->getUserGalleries()
						->where("userID", $this->getUser()->id)
						->order('id DESC');
		
		$templateName = "../MyUserGalleries/myUserGalleries.latte";
		
		$this->renderBase($mode, $galleries, $this->getUser()->id, $templateName);
	}

}

?>
 