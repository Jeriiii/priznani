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

	/** @var \NetteExt\DeviceDetector @inject */
	public $deviceDetector;

	/** Třída pro nastavování (mobilních) šablon
	 * @var \NetteExt\TemplateManager\TemplateManager @inject */
	public $templateManager;

	protected function startup() {
		parent::startup();
		$this->testMode = $this->context->parameters["testMode"];
		$this->productionMode = $this->context->parameters["productionMode"];
		if ($this->deviceDetector->isMobile()) {
			$this->templateManager->setTemplates($this, new Mvl());
		}
	}

	/**
	 * Zkontroluje, zda je uživatel přihlášen. Pokud ne, přesměruje ho na přihlášení.
	 */
	protected function checkLoggedIn() {
		$user = $this->getUser();
		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			} else {
				$this->flashMessage('Nejdříve se musíte přihlásit');
			}
			$backlink = $this->backlink();
			$httpRequest = $this->context->getByType('Nette\Http\Request');
			$backquery = $httpRequest->getQuery();
			$backlinkSession = $this->getSession('backlink');
			$backlinkSession->link = $backlink;
			$backlinkSession->query = $backquery;
			$this->redirect(':Sign:in', array('backlink' => TRUE));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
				$this->redirect(':Homepage:');
			}
		}
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
