<?php

use NetteExt\Path\ImagePathCreator;
use NetteExt\Path\GalleryPathCreator;

/**
 * BaseHelperPresenter Description
 */
class BaseProjectPresenter extends Nette\Application\UI\Presenter {

	public function actionDefault() {

	}

	public function renderDefault() {

	}

	/**
	 * Helper pro relativní cestu k obrázku
	 * @param type $class
	 * @return type
	 */
	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		$template->registerHelper('getImgPath', function ($image) {
			$basePath = $this->context->httpRequest->url->basePath;
			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($image->galleryID, $image->gallery->userID);
			$imgPath = ImagePathCreator::getImgPath($image->id, $image->suffix, $galleryFolder, $basePath);
			return $imgPath;
		});
		return $template;
	}

}
