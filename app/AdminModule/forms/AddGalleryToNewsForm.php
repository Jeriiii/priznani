<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class AddGalleryToNewsForm extends Form
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
		
		$galleries = $presenter->context->createGalleries()
					->fetchPairs("id", "name");
		
		$this->addSelect("id_gallery", "Připojit galerii:", $galleries)
			->setPrompt("- vyberte galerii -");
		
		$this->addSubmit("submit", "Připojit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(AddGalleryToNewsForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$id_new = $presenter->id_new;
		
		$exist_row = $presenter->context->createNews_galleries()
				->where("id_gallery", $values->id_gallery)
				->where("id_new", $id_new)
				->fetch();
		
		if( empty($exist_row) )
			$presenter->context->createNews_galleries()
				->insert(array(
					"id_gallery" => $values->id_gallery,
					"id_new" => $id_new,
				));

		$this->getPresenter()->flashMessage('Galerie byla připojena');
		$this->getPresenter()->redirect('AdminNews:changeNew', $id_new);
		
 	}
}
