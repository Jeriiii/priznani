<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class DatingRegistrationWomanThirdForm extends DatingRegistrationBaseWomanForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Do třetí části registrace')
				->setAttribute("class", "btn btn-success");

		return $this; 
	}
	public function submitted($form)
	{
		parent::submitted($form);
		$values = $form->values;
		
		$this->presenter->redirect('Datingregistration:register', $values->state, $values->orientation, 
				$values->tallness, $values->shape, $values->smoke, $values->drink,$values->graduation, $values->bra_size, $values->hair_colour,"","");
		
		
	}
	

}
