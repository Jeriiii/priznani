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

	/** @var TooltipHelper */
	public $toolTipHelper;

	/** @var ImageHelper */
	public $imageHelper;

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
		$this->toolTipHelper = new TooltipHelper;
		$this->imageHelper = new ImageHelper($this->getImgPathHelper);
	}

	/**
	 * Zaregistruje všechny helpery
	 * @param Nette\Templating\ITemplate $template
	 */
	public function registerHelpers($template) {
		$this->registerGetImgPathHelpers($template);
		$this->registerShowProfHelpers($template);
		$this->registerShowUserDataHelpers($template);
		$this->registerToolTipHelpers($template);
		$this->registerImageHelpers($template);
	}

	/**
	 * Zaregistruje helper na vytváření obrázků.
	 * @param type $template
	 */
	private function registerImageHelpers($template) {
		$imageHelper = $this->imageHelper;

		$template->registerHelper(ImageHelper::NAME, function($image, $class = '', $style = '') use ($imageHelper) {
			return $imageHelper->img($image, GetImgPathHelper::TYPE_USER_GALLERY, $class, $style);
		});
		$template->registerHelper(ImageHelper::NAME_MIN, function($image, $class = '', $style = '') use ($imageHelper) {
			return $imageHelper->imgMin($image, GetImgPathHelper::TYPE_USER_GALLERY, $class, $style);
		});
		$template->registerHelper(ImageHelper::NAME_MIN_SQR, function($image, $class = '', $style = '') use ($imageHelper) {
			return $imageHelper->imgSqr($image, GetImgPathHelper::TYPE_USER_GALLERY, $class, $style);
		});
		$template->registerHelper(ImageHelper::NAME_SCRN, function($image, $class = '', $style = '') use ($imageHelper) {
			return $imageHelper->imgScrn($image, GetImgPathHelper::TYPE_USER_GALLERY, $class, $style);
		});
		$template->registerHelper(ImageHelper::NAME_STREAM, function($image, $class = '', $style = '') use ($imageHelper) {
			return $imageHelper->imgStream($image, GetImgPathHelper::TYPE_USER_GALLERY, $class, $style);
		});
	}

	/**
	 * Zaregistruje ShowProf helper
	 * @param type $template
	 */
	private function registerShowProfHelpers($template) {
		$showProfHelper = $this->showProfHelper;
		$template->registerHelper(ShowProfHelper::NAME, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, FALSE, $minSize);
		});
		$template->registerHelper(ShowProfHelper::NAME_MIN, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, TRUE, $minSize);
		});
		$template->registerHelper(ShowProfHelper::NAME_MIN_DIV, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, TRUE, $minSize, "div");
		});
		$template->registerHelper(ShowProfHelper::NAME_DIV, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, FALSE, $minSize, "div");
		});
		$template->registerHelper(ShowProfHelper::NAME_NO_LINK, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, TRUE, $minSize, "span", TRUE);
		});
		$template->registerHelper(ShowProfHelper::NAME_SEARCH, function($user, $href = null, $minSize = TRUE) use ($showProfHelper) {
			return $showProfHelper->showProf($user, $href, FALSE, $minSize, "div", FALSE, TRUE);
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
	 * Zaregistruje hepery pro zobrazení více informací u otazníčku
	 * @param type $template
	 */
	private function registerToolTipHelpers($template) {
		$toolTipHelper = $this->toolTipHelper;
		$template->registerHelper(TooltipHelper::TOOL_TIP, function($infoText, $infoEl = "?") use ($toolTipHelper) {
			return $toolTipHelper->createToolTip($infoText, $infoEl);
		});
		$template->registerHelper(TooltipHelper::TOOL_TIP_MOBILE, function($infoText, $infoEl = "?") use ($toolTipHelper) {
			return $toolTipHelper->createToolTipMobile($infoText, $infoEl);
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
