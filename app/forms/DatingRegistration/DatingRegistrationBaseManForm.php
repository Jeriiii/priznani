<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

/*
 * rozšiřuje DatingRegistrationBaseForm o konkrétní věci pro muže
 */

class DatingRegistrationBaseManForm extends DatingRegistrationBaseForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->addSelect('penis_length', 'Délka penisu:', \Users::getUserPenisLengthOption());
		$this->addSelect('penis_width', 'Šířka penisu:', \Users::getUserPenisWidthOption());
	}
	public function submitted($form)
	{
		parent::submitted($form);
	}
	

}
