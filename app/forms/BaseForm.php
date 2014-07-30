<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Mail\Message;
use POS\Model\UserDao;

class BaseForm extends Form {

	/** @var boolean Je spuštěno testování Behatem? */
	protected $testMode;

	/** @var boolean Je spuštěna aplikace na produkci? */
	protected $productionMode;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->testMode = $this->getPresenter()->testMode;
		$this->productionMode = $this->getPresenter()->productionMode;
	}

}
