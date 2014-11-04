<?php

namespace POSComponent\Galleries\UserImagesGalleryThumbnails;

class CompetitionsGalleryIamgesThumbnails extends BaseUserGalleryImagesThumbnails {

	public function render($mode) {
		$templateName = "../CompetitionsGalleryImagesThumbnails/competitionsGalleryImagesThumbnails.latte";

		$this->renderBase($mode, $this->getUser()->id, $templateName);
	}

}

?>
