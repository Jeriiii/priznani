<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Forms\Controls,
	Nette\Utils\Strings as Strings;

use Nette\Image;


class NewChangeForm extends Form
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
	$presenter = $this->getPresenter();
	$this->id = $presenter->id_new;
	
	$new = $presenter->context->createNews()
				->where('id', $this->id)
				->fetch();
	$this->addText("name", "Jméno aktuality", 30, 150)
		->setDefaultValue($new->name)
		->addRule(Form::FILLED,"Vyplňte jméno aktuality");
	$this->addTextArea('content', 'Text aktuality:', 100,15)
		->setDefaultValue($new->content)
		->getControlPrototype()->class('mceEditor');
    	$this->addSubmit('send', 'Změnit');
    	//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
    public function submitted(NewChangeForm $form)
	{
		$values = $form->values;
		$presenter = $form->getPresenter();
		
		$values->url = Strings::webalize($values->name);

		$presenter->context->createNews()
			->where("id", $this->id)
			->update($values);
		
		$presenter->flashMessage('Aktualita byla úspěšně změněna');
		$presenter->redirect("AdminNews:changeNew",  $this->id);
	}
}
