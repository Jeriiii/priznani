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
		$linkCallback = callback($this->presenter, "link");
		$helperRegistrator = new HelperRegistrator($url, $linkCallback);
		$helperRegistrator->registerHelpers($template);

		return $template;
	}

	/**
	 * Nastaví šablonu a vyrendruje ji. Je to poslední funkce v renderu.
	 * @param string $templatePath Celá cesta k šabloně.
	 */
	protected function renderTemplate($templatePath) {
		$template = $this->template;
		$template->setFile($templatePath);
		$template->render();
	}

}
