<?php

use NetteExt\Helper\HelperRegistrator;

/**
 * BaseHelperPresenter Description
 */
class BaseProjectPresenter extends Nette\Application\UI\Presenter {

	/** @var boolean Říká, jestli je aplikace spuštěna testovacím nástrojem Behat */
	public $testMode;

	/** @var boolean Říká, jestli je aplikace spuštěna na produkci */
	public $productionMode;

	protected function startup() {
		parent::startup();
		$this->testMode = $this->context->parameters["testMode"];
		$this->productionMode = $this->context->parameters["productionMode"];
	}

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
