<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\CropImageUpload;

use Nette\Application\UI\Form\BaseForm;
use POSComponent\BaseProjectControl;

/**
 * Zprostředkovává upload obrázku, který posléze umožňuje oříznout
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class CropImageUpload extends BaseProjectControl {

	/**
	 * @var BaseForm
	 */
	public $formToUpload;

	/**
	 * Standardní konstruktor
	 */
	function __construct(BaseForm $formToUpload = NULL, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->formToUpload = $formToUpload;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/cropImageUpload.latte');

		$template->render();
	}

	/**
	 * komponenta formuláře, který nejdříve nahraje fotku
	 * @param type $name
	 * @return \Nette\Application\UI\Form\BaseForm
	 */
	protected function createComponentUploadForm($name) {
		return $this->formToUpload;
	}

	/**
	 * komponenta formuláře předaného v konstruktoru
	 * @param type $name
	 * @return \Nette\Application\UI\Form\BaseForm
	 */
	protected function createComponentFirstImageUploadForm($name) {
		new \Nette\Application\UI\Form\FirstImageUploadForm(WWW_DIR . '/image-temp/', $this, $name);
	}

}
