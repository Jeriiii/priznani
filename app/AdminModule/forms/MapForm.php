<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class  MapForm extends Form
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
		
		$map = $presenter->context->createMap()
				->fetch();
		
		$this->addText("name", "Název:", 30, 200);
		$this->addText("gps","GPS:", 30, 100)
			->addRule(Form::FILLED, "gps souřadnice musí být vyplněna");
		$this->addTextArea("text", "Text:", 30);
		
		if($map)
			$this->setDefaults(array(
				"name" => $map->name,
				"text" => $map->text,
				"gps" => $map->gps,
			));
		
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(MapForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		$facebook = $presenter->context->createMap()
				->fetch();
		if(!empty( $facebook) )
		{
				$presenter->context->createMap()->update(
					$values
				);
		}else{
				$presenter->context->createMap()->insert(
					$values
				);
		}
		$this->getPresenter()->flashMessage('Mapa byla změněna.');
		$this->getPresenter()->redirect("this");
 	}
}
