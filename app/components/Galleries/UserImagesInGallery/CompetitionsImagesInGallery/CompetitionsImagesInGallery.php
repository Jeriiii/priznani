<?php

namespace POSComponent\Galleries\UserImagesInGallery;

class CompetitionsImagesInGallery extends BaseUserImagesInGallery {

	public function render($mode) {
		$templateName = "../CompetitionsImagesInGallery/competitionsImagesInGallery.latte";

		$this->renderBase($mode, $this->getUser()->id, $templateName);
	}

}

?>
