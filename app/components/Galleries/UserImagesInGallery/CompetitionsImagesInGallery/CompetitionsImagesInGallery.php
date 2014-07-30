<?php

/**
 * Základní komponenta pro vykreslení obrázků v galerii
 *
 * @author Mario
 */

namespace POSComponent\Galleries\UserImagesInGallery;

use POS\Model\UserDao;
use POSComponent\BaseProjectControl;

class CompetitionsImagesInGallery extends BaseUserImagesInGallery {

	public function render($mode) {
		$templateName = "../CompetitionsImagesInGallery/competitionsImagesInGallery.latte";

		$this->renderBase($mode, $this->getUser()->id, $templateName);
	}

}

?>
