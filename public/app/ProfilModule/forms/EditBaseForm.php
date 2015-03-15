<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Mail\Message;

class EditBaseForm extends Form {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
	}

}
