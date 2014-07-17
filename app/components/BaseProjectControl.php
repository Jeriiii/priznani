<?php

namespace POSComponent;

use Nette\Application\UI\Control;
use NetteExt\Helper\HelperRegistrator;

/**
 * Nejzákladnější komponenta pro další komponenty
 */
class BaseProjectControl extends Control {

	/**
	 * Zaregistruje helpery
	 * @param type $class
	 * @return type
	 */
	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);

		$url = $this->presenter->context->httpRequest->url;

		$helperRegistrator = new HelperRegistrator($url);
		$helperRegistrator->registerHelpers($template);

		return $template;
	}

}