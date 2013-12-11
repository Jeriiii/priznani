<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class AddFormForm extends Form
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
		$forms = $presenter->context->createForms()
					->fetchPairs("id", "name");
		
//		$form = $presenter->context->createForms()
//					->where("id_page", $presenter->id_page)
//					->fetch();
		
		$form = $presenter->context->createPages_forms()
					->getForm($presenter->id_page)
					->fetch();
		
		$forms["null"] = "- odpojit formulář -";
		
		$id_form = NULL;
		
		if( !empty ($form))
			$id_form = $form->id;
		
		$this->addSelect("id_form", "Připojený formulář:", $forms)
				->setPrompt("- vyberte formulář nebo akci -")
				->addRule(Form::FILLED, "Nejdřív musíte vybrat galerii");
		
		$this->setDefaults(array(
			"id_form" => $id_form
		));
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(AddFormForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$id_page = $presenter->id_page;
		
//		$presenter->context->createForms()
//			->where("id_page", $id_page)
//			->update(array(
//				"id_page" => NULL,
//			));
		$exist_form = $presenter->context->createPages_forms()
			->where("id_page", $id_page)
			->fetch();
		
		if($values->id_form != "null")
		{
			if(!empty ($exist_form))
			{
				$presenter->context->createPages_forms()
					->where("id_form", $values->id_form)
					->update(array(
						"id_page" => $id_page,
					));
			}else{
				$presenter->context->createPages_forms()
					->insert(array(
						"id_page" => $id_page,
						"id_form" => $values->id_form
					));
			}
//			$presenter->context->createForms()
//				->where("id", $values->id_form)
//				->update(array(
//					"id_page" => $id_page,
//				));
			$presenter->flashMessage('Formulář byl připojen.');
		}else{
			if(!empty ($exist_form))
			{
				$presenter->context->createPages_forms()
						->where("id_page", $id_page)
						->delete();
			}
			$presenter->flashMessage('Formulář byl odpojen.');
		}
		
		$this->getPresenter()->redirect('Pages:changePage', $id_page);
		
 	}
}
