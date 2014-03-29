<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Forms\Controls,
	Nette\Utils\Strings as Strings;

use Nette\Image;


class UserGalleryImageChangeForm extends Form
{
	private $galleryID;
        private $imageID;
    
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

	$this->galleryID = $presenter->galleryID;
        $this->imageID = $presenter->imageID;

	$filledForm = $presenter->context->createUsersImages()
				->where('galleryID', $this->galleryID)
                                ->where('id', $this->imageID)
				->fetch();
        
        $this->addText('name', 'Jméno:')
                        ->setDefaultValue($filledForm->name)
                        ->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40)
                        ->addRule(Form::FILLED, 'Zadejte jméno fotky');
        
	$this->addTextArea('description', 'Popis fotky:', 100,15)
		->setDefaultValue($filledForm->description)
                ->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500)
		->addRule(Form::FILLED,"Vyplňte popis fotky");
    	$this->addSubmit('send', 'Změnit');
    	//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
    public function submitted(UserGalleryImageChangeForm $form)
	{
		$values = $form->values;
		$presenter = $form->getPresenter();
		
                $values2['description'] = $values->description;

		$presenter->context->createUsersImages()
			->where("id", $this->imageID)
			->update($values2);
		
		$presenter->flashMessage('Fotka byla úspěšně opravena');
		$presenter->redirect("Galleries:listUserGalleryImages", $this->galleryID);
	}
}
