<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;
use POS\Model\PartyDao;

class PartyConfessionForm extends BaseInsertForm {

	public function __construct(PartyDao $partyDao, IContainer $parent = NULL, $name = NULL) {
		$this->table_name = "party";
		$this->partyDao = $partyDao;
		return parent::__construct($parent, $name);
	}

	public function submitted(PartyConfessionForm $form) {
		$this->baseSubmitted($form, "partyConfession");
	}

}
