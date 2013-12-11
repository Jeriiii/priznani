<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class AddGalleryForm extends Form
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
		$id_page = $presenter->id_page;
		
		$galleries = $presenter->context->createGalleries()
					->fetchPairs("id", "name");
		
		$this->addSelect("id_gallery", "Připojit galerii:", $galleries)
			->setPrompt("- vyberte galerii -")
			->addRule(Form::FILLED, "Nejdřív musíte vybrat galerii");
		
		$this->addSubmit("submit", "Připojit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(AddGalleryForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$id_page = $presenter->id_page;
		
		$exist_row = $presenter->context->createPages_galleries()
				->where("id_gallery", $values->id_gallery)
				->where("id_page", $id_page)
				->fetch();
		
		if( empty($exist_row) )
			$presenter->context->createPages_galleries()
				->insert(array(
					"id_gallery" => $values->id_gallery,
					"id_page" => $id_page,
				));

		$this->getPresenter()->flashMessage('Galerie byla připojena');
		$this->getPresenter()->redirect('Pages:changePage', $id_page);
		
 	}
}
