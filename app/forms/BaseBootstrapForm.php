<?php

namespace Nette\Application\UI\Form;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BaseBootstrapForm extends BaseForm {

	public function __construct(\Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->setRenderer(new BootstrapRenderer);
	}

}
