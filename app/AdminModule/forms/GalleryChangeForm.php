<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class GalleryChangeForm extends Form
{
	private $id_gallery;
	
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
		$this->id_gallery = $presenter->id_gallery;
		$gallery = $presenter->context->createGalleries()
					->where("id", $this->id_gallery)
					->fetch();
		
		$this->addText("name", "Jméno", 30, 150)
			->setDefaultValue($gallery->name)
			->addRule(Form::FILLED, "Musíte zadat jméno galerie.");
		$this->addSubmit("submit", "Změnit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(GalleryChangeForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$presenter->context->createGalleries()
			->where("id", $presenter->id_gallery)
			->update($values);
		$presenter->flashMessage('Galerie byla změněna');
		$presenter->redirect('Galleries:gallery', $this->id_gallery);
 	}
}
