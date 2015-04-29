<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Stream na profilové stránce uživatele.
 *
 * @author Mario
 */

namespace POSComponent\Stream;

use \POSComponent\Stream\BaseStream\BaseStream;

class ProfilStream extends BaseStream {

	public function render() {
		$mode = 'profilStream';
		if ($this->getEnvironment()->isMobile()) {
			$templateName = "../ProfilStream/profilStreamMobile.latte";
		} else {
			$templateName = "../ProfilStream/profilStream.latte";
		}


		$this->renderBase($mode, $templateName);
	}

}
