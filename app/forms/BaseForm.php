<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Mail\Message;
use POS\Model\UserDao;
use Nette\Forms\Controls;

class BaseForm extends Form {

	/** @var boolean Je spuštěno testování Behatem? */
	protected $testMode;

	/** @var boolean Je spuštěna aplikace na produkci? */
	protected $productionMode;

	/*	 * ** BOOTSTRAP PROMĚNNÉ - použijí se jen při zavolání metody na boostrap vykreslení ** * */

	/** @var string Třída celého formuláře */
	private $formClass = "";

	/** @var string Třída primárního tlačítka */
	private $primaryBtnClass = "btn-main medium";

	/** @var string Třída labelu */
	private $lableClass = "";

	/** @var string Třída inputu */
	private $inputClass = "";

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->testMode = $this->getPresenter()->testMode;
		$this->productionMode = $this->getPresenter()->productionMode;
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
		$renderer->wrappers['control']['container'] = 'div class="' . $this->inputClass . '"';
		$renderer->wrappers['label']['class'] = '';

		// make form and controls compatible with Twitter Bootstrap
		$this->getElementPrototype()->class($this->formClass);

		foreach ($this->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? $this->primaryBtnClass : 'btn btn-default');
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

	public function setBFormToHorizontal($lableCCols = "col-sm-2", $inputCCols = "col-sm-5") {
		$this->lableClass = $this->lableClass . $lableCCols . " " . "control-label";
		$this->inputClass = $this->inputClass . $inputCCols;
		$this->formClass = "form-horizontal";
	}

}
