<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Registruje další helpery do šablony
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

class HelperRegistrator {

	/** @var GetImgPathHelper */
	public $getImgPathHelper;

	/** @var ShowProfHelper */
	public $showProfHelper;

	/** @var ShowUserDataHelper */
	public $showUserDataHelper;

	/** @var array Callback na fci link */
	private $linkCallback;

	/**
	 * @param \Nette\Http\Url $url
	 * @param array $linkCallback Callback na fci link
	 */
	public function __construct($url, $linkCallback) {
		$this->getImgPathHelper = new GetImgPathHelper($url);
		$this->linkCallback = $linkCallback;
		$this->showProfHelper = new ShowProfHelper($this->getImgPathHelper, $linkCallback);
		$this->showUserDataHelper = new ShowUserDataHelper();
	}

	/**
	 * Zaregistruje všechny helpery
	 * @param Nette\Templating\ITemplate $template
	 */
	public function registerHelpers($template) {
		$this->registerGetImgPathHelpers($template);
		$this->registerShowProfHelpers($template);
		$this->registerShowUserDataHelpers($template);
	}

	private function registerShowProfHelpers($template) {
		$showProfHelper = $this->showProfHelper;
		$template->registerHelper(ShowProfHelper::NAME, function($user, $href = null) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, FALSE);
		});
		$template->registerHelper(ShowProfHelper::NAME_MIN, function($user, $href = null) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, TRUE);
		});
		$template->registerHelper(ShowProfHelper::NAME_MIN_DIV, function($user, $href = null) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, TRUE, "div");
		});
		$template->registerHelper(ShowProfHelper::NAME_DIV, function($user, $href = null) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, FALSE, "div");
		});
	}

	/**
	 * Zaregistruje hepery pro získávání dat o profilu
	 * @param type $template
	 */
	private function registerShowUserDataHelpers($template) {
		$showUserHelper = $this->showUserDataHelper;
		$template->registerHelper(ShowUserDataHelper::SEXY_LABEL_NAME, function($user) use ($showUserHelper) {
			return $showUserHelper->showSexyLabel($user);
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
