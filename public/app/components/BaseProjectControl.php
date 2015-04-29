<?php

namespace POSComponent;

use Nette\Application\UI\Control;
use NetteExt\Helper\HelperRegistrator;
use NetteExt\DeviceDetector;

/**
 * Nejzákladnější komponenta pro další komponenty
 */
class BaseProjectControl extends Control {

	/** @var boolean|NULL Říká, jestli je aplikace spuštěna testovacím nástrojem Behat. NULL = proměnná nebyla nastavena */
	private $testMode = NULL;

	/** @var boolean|NULL Říká, jestli je aplikace spuštěna na produkci. NULL = proměnná nebyla nastavena */
	private $productionMode = NULL;

	/** @var DeviceDetector detektor vlastností zařízení */
	private $deviceDetector = NULL;

	/*	 * ******************** Metody pro práci s módy *************************** */

	public function setTestMode() {
		$this->testMode = TRUE;
		$this->productionMode = FALSE;
	}

	public function setProductionMode() {
		$this->productionMode = TRUE;
		$this->testMode = FALSE;
	}

	public function setMode($presenter) {
		if (!($presenter instanceof \Nette\Application\UI\Presenter)) {
			throw new Exception("variable $presenter must be instance of presenter");
		}

		$this->testMode = $this->presenter->context->parameters["testMode"];
		$this->productionMode = $this->presenter->context->parameters["productionMode"];
	}

	public function isTestMode() {
		if ($this->testMode === NULL || $this->productionMode === NULL) {
			throw new Exception("You must call method setMode first");
		}

		return $this->testMode;
	}

	public function isProductionMode() {
		if ($this->testMode === NULL || $this->productionMode === NULL) {
			throw new Exception("You must call method setMode first");
		}

		return $this->productionMode;
	}

	/*	 * ****************************************************** */

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

	public function setPresenter($presenter) {
		$this->presenter = $presenter;
	}

	/**
	 * Vytvoří nový detektor zařízení, použít pokud není možné jej injectnout.
	 * @return DeviceDetector
	 */
	public function getDeviceDetector() {
		if (!$this->getPresenter()) {
			throw new Exception('Cannot detect device without presenter attached to control.');
		}
		if (empty($this->deviceDetector)) {
			$this->deviceDetector = new DeviceDetector($this->getPresenter()->getSession());
		}
		return $this->deviceDetector;
	}

	/**
	 * Vrátí prostředí, na němž aplikace právě běží
	 * @return \NetteExt\EnvironmentDetector
	 */
	public function getEnvironment() {
		if (!$this->getPresenter()) {
			throw new Exception('Cannot detect environment without presenter attached to control.');
		}
		if (empty($this->getPresenter()->environment)) {
			throw new Exception('The presenter has to have initialized environment detector variable.');
		}
		return $this->getPresenter()->environment;
	}

}
