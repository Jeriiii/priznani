<?php

<#assign licenseFirst = "/* ">
<#assign licensePrefix = " * ">
<#assign licenseLast = " */">
<#include "${project.licensePath}">

namespace App\Forms;

use Nette\Forms\Form;

/**
 * Popis formuláře
 *
 * @author ${user}
 */
class {$name} extends BaseForm {

	public function __construct($parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addSubmit('send', 'Odeslat');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted({$name} $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$presenter->flashMessage('message');
		$presenter->redirect('this');
	}

}
