<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class  GoogleAnalyticsForm extends Form
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
		
		$google_analytics = $presenter->context->createGoogle_analytics()
				->fetch();
		
		$this->addText("name", "osobní číslo:", 30, 500)
			->setDefaultValue( !empty( $google_analytics ) ? $google_analytics->name : "");
		
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(GoogleAnalyticsForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		$facebook = $presenter->context->createGoogle_analytics()
				->fetch();
		if(!empty( $facebook) )
		{
				$presenter->context->createGoogle_analytics()->update(array(
					"name" => $values->name
				));
		}else{
				$presenter->context->createGoogle_analytics()->insert(array(
					"name" => $values->name
				));
		}
		$this->getPresenter()->flashMessage('Osobní číslo bylo změněno.');
		$this->getPresenter()->redirect("this");
 	}
}
