<?php

use NetteExt\Helper\HelperRegistrator;
use Nette\Security\User;

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
		$linkCallback = callback($this, "link");

		$helperRegistrator = new HelperRegistrator($url, $linkCallback);
		$helperRegistrator->registerHelpers($template);

		return $template;
	}

	/**
	 * Připojí všechny potřebné js soubory KROMNĚ jquery a jquery-ui (zde na ui stačí
	 * soubor jquery-ui-1.9.2.timepicker.min.js).
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createComponentJsTimePicker() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js/timePicker');
		$files->addFiles(array(
			'timepicker.js',
			'jquery-ui-timepicker-cs.js',
			'init.js',
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');

		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});

		// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

}
