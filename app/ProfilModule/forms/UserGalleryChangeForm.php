<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Forms\Controls,
	Nette\Utils\Strings as Strings;

use Nette\Image;


class UserGalleryChangeForm extends Form
{
	private $galleryID;
    
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
    //    \Nette\Diagnostics\Debugger::Dump($presenter->galleryID);die();
	$this->galleryID = $presenter->galleryID;
	
	$filledForm = $presenter->context->createUsersGalleries()
				->where('id', $this->galleryID)
				->fetch();
        
	$this->addText("name", "Jméno galerie", 30, 150)
		->setDefaultValue($filledForm->name)
		->addRule(Form::FILLED,"Vyplňte jméno galerie")
                ->addRule(Form::MAX_LENGTH, "Maximální délka jména galerie je %d znaků", 150);
	$this->addTextArea('description', 'Popis galerie:', 100,15)
		->setDefaultValue($filledForm->description)
                ->addRule(Form::MAX_LENGTH, "Maximální délka popisu galerie je %d znaků", 500)
		->addRule(Form::FILLED,"Vyplňte popis galerie");
    	$this->addSubmit('send', 'Změnit');
    	//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
        public function submitted(UserGalleryChangeForm $form)
	{
		$values = $form->values;
		$presenter = $form->getPresenter();
		
		$values2['name'] = $values->name;
                $values2['description'] = $values->description;

		$presenter->context->createUsersGalleries()
			->where("id", $this->galleryID)
			->update($values2);
		
		$presenter->flashMessage('Galerie byla úspěšně změněna');
		$presenter->redirect("Galleries:");
	}
}
