<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class  FacebookForm extends Form
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
		$presenter = $this->getPresenter();
		
		$facebook = $presenter->context->createFacebook()
				->fetch();
		
		$this->addText("url", "url adresa:", 30, 500)
			->setDefaultValue( !empty( $facebook ) ? $facebook->url : "");
		
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(FacebookForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		$facebook = $presenter->context->createFacebook()
				->fetch();
		if(!empty( $facebook) )
		{
				$presenter->context->createFacebook()->update(array(
					"url" => $values->url
				));
		}else{
				$presenter->context->createFacebook()->insert(array(
					"url" => $values->url
				));
		}
		$this->getPresenter()->flashMessage('Url adresa byla změněna.');
		$this->getPresenter()->redirect("this");
 	}
}
