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
                $users = $this->getPresenter()->context->createUsers();     
                
		$this->addSelect('penis_length', 'Délka penisu:', $users::getUserPenisLengthOption());
		$this->addSelect('penis_width', 'Šířka penisu:', $users::getUserPenisWidthOption());
	}
	public function submitted($form)
	{
		parent::submitted($form);
	}
	

}
