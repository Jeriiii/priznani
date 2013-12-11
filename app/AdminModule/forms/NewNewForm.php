<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Utils\Strings as Strings;


class NewNewForm extends Form
{
	private $id;
    
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
	$this->addText("name", "Jméno aktuality", 30, 150)
		->addRule(Form::FILLED,"Vyplňte jméno aktuality");
	$this->addTextArea('content', 'Text aktuality:', 100,15)
		->getControlPrototype()->class('mceEditor');
    	$this->addSubmit('send', 'Vytvořit');
    	$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
    public function submitted(NewNewForm $form)
	{
		$values = $form->values;
		$values->create = new DateTime();
		$presenter = $form->getPresenter();
		
		$values->url = Strings::webalize($values->name);	

		$presenter->context->createNews()
			->insert($values);

		$presenter->flashMessage('Aktualita byla vytvořena');
		$presenter->redirect("AdminNews:");
	}
	
}