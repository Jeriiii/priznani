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

	/** @var ShowUserHelper */
	public $showUserHelper;

	/** @var array Callback na fci link */
	private $linkCallback;

	/**
	 * @param \Nette\Http\Url $url
	 * @param array $linkCallback Callback na fci link
	 */
	public function __construct($url, $linkCallback) {
		$this->getImgPathHelper = new GetImgPathHelper($url);
		$this->linkCallback = $linkCallback;
		$this->showUserHelper = new ShowUserHelper($this->getImgPathHelper, $linkCallback);
	}

	/**
	 * Zaregistruje všechny helpery
	 * @param Nette\Templating\ITemplate $template
	 */
	public function registerHelpers($template) {
		$this->registerGetImgPathHelpers($template);
		$this->registerShowUserHelpers($template);
	}

	private function registerShowUserHelpers($template) {
		$showUserHelper = $this->showUserHelper;
		$template->registerHelper(ShowUserHelper::NAME, function($user, $typeEl = ShowUserHelper::TYPE_EL_SPAN) use ($showUserHelper) {
			return $showUserHelper->showUserMin($user, $typeEl);
		});
	}

	/**
	 * Zaregistruje GetImgPath helpery
	 * @param Nette\Templating\ITemplate $template
	 */
	private function registerGetImgPathHelpers($template) {
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
