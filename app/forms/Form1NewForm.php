<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;

class Form1NewForm extends BaseInsertForm {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		$this->table_name = "about_sex";
		return parent::__construct($parent, $name);
	}

	public function submitted(Form1NewForm $form) {
		$this->baseSubmitted($form, "confession");
	}

}
