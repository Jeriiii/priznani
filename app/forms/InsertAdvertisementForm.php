<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;

class InsertAdvertisementForm extends BaseInsertForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		$this->table_name = "date";
		return parent::__construct($parent, $name);
	}
    
	public function submitted(InsertAdvertisementForm $form)
	{
		$this->baseSubmitted($form, NULL);
 	}
}
