<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class GalleryNewForm extends Form
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
		$this->addText("name", "Jméno", 30, 150)
			->addRule(Form::FILLED, "Musíte zadat jméno galerie.");
		$this->addSubmit("submit", "Vytvořit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(GalleryNewForm $form)
	{
		$values = $form->values;
		$this->getPresenter()->context->createGalleries()
			->insert($values);
		$this->getPresenter()->flashMessage('Galerie byla vytvořena');
		$this->getPresenter()->redirect('Galleries:galleries');
 	}
}
