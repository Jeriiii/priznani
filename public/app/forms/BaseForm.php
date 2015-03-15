<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Mail\Message;
use POS\Model\UserDao;
use Nette\Forms\Controls;
use NetteExt\DeviceDetector;

class BaseForm extends Form {

	/** @var boolean Je spuštěno testování Behatem? */
	protected $testMode;

	/** @var boolean Je spuštěna aplikace na produkci? */
	protected $productionMode;

	/** @var \NetteExt\DeviceDetector detektor zařízení */
	public $deviceDetector;

	/*	 * ** BOOTSTRAP PROMĚNNÉ - použijí se jen při zavolání metody na boostrap vykreslení ** * */

	/** @var string Třída celého formuláře */
	private $formClass = "";

	/** @var string Třída primárního tlačítka */
	private $primaryBtnClass = "btn-main medium";

	/** @var string Třída labelu */
	private $lableClass = "control-label";

	/** @var string Třída inputu */
	private $inputClass = "controls";

	/** @var string Element do kterého bude vložen input */
	private $inputContainer = "div";

	/** @var boolean TRUE = Ajaxové zpracování formuláře */
	private $ajax = FALSE;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$presenter = $this->getPresenter();
		if (!empty($presenter) && $presenter instanceof \Nette\Application\UI\Presenter) {
			$this->testMode = $this->getPresenter()->testMode;
			$this->productionMode = $this->getPresenter()->productionMode;
			$this->deviceDetector = new DeviceDetector($this->getPresenter()->getSession());
		}
	}

	/**
	 * Nastaví ajax zpracování.
	 */
	protected function ajax() {
		$this->getElementPrototype()->addClass('ajax');
		$this->formClass .= " ajax"; //protoze pri bootstraprenderu by se to prepsalo
		$this->ajax = TRUE;
	}

	/**
	 * Smaže všechna data co jsou vyplněná v polích. Používá se zpravidla
	 * po zpracování ajaxem v metodě submited.
	 */
	public function clearFields() {
		$this->setValues(array(), TRUE);
	}

	/**
	 * Nastaví vykreslování jako boostrap. Volá se až PO definici formuláře
	 */
	protected function setBootstrapRender() {
		// setup form rendering
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = $this->getControlContainer();
		$renderer->wrappers['label']['class'] = '';

		// make form and controls compatible with Twitter Bootstrap
		$this->getElementPrototype()->class($this->formClass);

		foreach ($this->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$btnClass = empty($usedPrimary) ? $this->getPrimaryBtwClass() : 'btn btn-default';
				$control->getControlPrototype()->addClass($btnClass);
				$usedPrimary = TRUE;
			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');
				$control->labelPrototype->addClass($this->lableClass);
			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}
	}

	/*	 * * BOOTSTRAP SETTRY ** */

	public function setBClassPrimaryBtn() {
		$this->primaryBtnClass = "btn btn-primary";
	}

	public function setBFormToInline() {
		$this->formClass = "form-inline";
	}

	public function setInputClass($name) {
		$this->inputClass = $name;
	}

	public function setBFormToHorizontal($lableCCols = "col-sm-2", $inputCCols = "col-sm-5") {
		$this->lableClass = $this->lableClass . $lableCCols . " " . "control-label";
		$this->inputClass = $this->inputClass . $inputCCols;
		$this->formClass = "form-horizontal";
	}

	/**
	 * getter vrací class pro primární tlačítko
	 * @return string Class tlačítka
	 */
	protected function getPrimaryBtwClass() {
		return $this->primaryBtnClass;
	}

	protected function setInputContainer($inputContainer) {
		$this->inputContainer = $inputContainer;
	}

	private function getControlContainer() {
		$class = empty($this->inputClass) ? "" : 'class="' . $this->inputClass . '"';
		$inputContainer = empty($this->inputContainer) ? "" : $this->inputContainer . ' ' . $class;
		return $inputContainer;
	}

}
