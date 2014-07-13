<?php

use NetteExt\Helper\HelperRegistrator;

/**
 * BaseHelperPresenter Description
 */
class BaseProjectPresenter extends Nette\Application\UI\Presenter {

	/**
	 * Zaregistruje helpery
	 * @param type $class
	 * @return type
	 */
	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);

		$url = $this->context->httpRequest->url;

		$helperRegistrator = new HelperRegistrator($url);
		$helperRegistrator->registerHelpers($template);

		return $template;
	}

}
