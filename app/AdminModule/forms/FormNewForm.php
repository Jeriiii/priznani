<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Utils\Html;


class FormNewForm extends Form
{
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		//graphics
		$renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = NULL;
		//form
		$types = array(
			1 => "typ 1",
			2 => "typ 2",
			3 => "typ 3",
		);
		
		$this->addText("name", "Jméno:", 30, 200)
			->addRule(Form::FILLED,"Zadejte prosím své jméno.");
		$this->addSelect("type", "Typ formuláře:", $types)
			->setPrompt("- vybrat typ -")
			->addRule(Form::FILLED, "Vyberte prosím typ formuláře.");
		$this->addSubmit("submit", "Vytvořit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(FormNewForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$presenter->context->createForms()
			->insert($values);
		$presenter->flashMessage('Formulář byl vytvořen', "success");
		$presenter->flashMessage('Formulář připojíte ke stránce v záložce STRÁNKY - vyberete stránku - PŘIPOJIT FORMULÁŘ', "block");
		$presenter->redirect("this");
 	}
}
