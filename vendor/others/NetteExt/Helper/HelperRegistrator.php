<?php

namespace NetteExt\Helper;

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Registruje další helpery do šablony
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class HelperRegistrator {

	/** @var GetImgPathHelper */
	public $getImgPathHelper;

	/**
	 * @param \Nette\Http\Url $url
	 */
	public function __construct($url) {
		$this->getImgPathHelper = new GetImgPathHelper($url);
	}

	/**
	 * Zaregistruje všechny helpery
	 * @param Nette\Templating\ITemplate $template
	 */
	public function registerHelpers($template) {
		$getImgPathHelper = $this->getImgPathHelper;
		$template->registerHelper(GetImgPathHelper::NAME, function($image, $gallType = GetImgPathHelper::TYPE_USER_GALLERY) use ($getImgPathHelper) {
			return $getImgPathHelper->getImgPath($image, $gallType);
		});
		$template->registerHelper(GetImgPathHelper::NAME_MIN, function($image, $gallType = GetImgPathHelper::TYPE_USER_GALLERY) use ($getImgPathHelper) {
			return $getImgPathHelper->getImgMinPath($image, $gallType);
		});
		$template->registerHelper(GetImgPathHelper::NAME_MIN_SQR, function($image, $gallType = GetImgPathHelper::TYPE_USER_GALLERY) use ($getImgPathHelper) {
			return $getImgPathHelper->getImgSqrPath($image, $gallType);
		});
		$template->registerHelper(GetImgPathHelper::NAME_SCRN, function($image, $gallType = GetImgPathHelper::TYPE_USER_GALLERY) use ($getImgPathHelper) {
			return $getImgPathHelper->getImgScrnPath($image, $gallType);
		});
		$template->registerHelper(GetImgPathHelper::NAME_STREAM, function($streamItem, $gallType = GetImgPathHelper::TYPE_USER_GALLERY) use ($getImgPathHelper) {
			return $getImgPathHelper->getStreamImgPath($streamItem, $gallType);
		});
	}

}
