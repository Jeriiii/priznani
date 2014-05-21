<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

/*
 * rozšiřuje DatingRegistrationBaseForm o konkrétní věci pro ženu
 */

class DatingRegistrationBaseWomanForm extends DatingRegistrationBaseForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$UserBraOption = array(
			'a' => 'a',
			'b' => 'b',
			'c' => 'c',
			'd' => 'd',
			'e' => 'e',
		);
		$this->addSelect('bra_size', 'Velikost košíčků:', $UserBraOption);

		$this->addText('hair_colour', 'Barva vlasů:');
	}
	public function submitted($form)
	{
		parent::submitted($form);
	}
	

}
