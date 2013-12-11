<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class DatingRegistrationManFourthForm extends DatingRegistrationBaseManForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->addText('age', 'Věk')
			->addRule(Form::FILLED, 'Věk není vyplněn.')
			->addRule(Form::INTEGER, 'Věk není číslo.')
			->addRule(Form::RANGE, 'Věk musí být od %d do %d let.', array(18, 120));
		
		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Dokončit registraci')
				->setAttribute("class", "btn btn-success");;

		return $this; 
	}
	public function submitted($form)
	{
		parent::submitted($form);
		
		$values = $form->values;
		$this->presenter->redirect('Datingregistration:registerCouple', $values->age, $values->state, $values->orientation, 
				$values->tallness, $values->shape, $values->smoke, $values->drink, $values->graduation, "","", $values->penis_length, $values->penis_width);
	}
	

}
