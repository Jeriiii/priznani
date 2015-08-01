<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Utils\Strings as Strings;


class EditPageForm extends NewPageForm
{
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		
		$presenter = $this->getPresenter();
		
		$this->setDefaults(array(
			"name" => $presenter->page->name,
			"text" => $presenter->page->text,
			"accessRights" => $presenter->page->access_rights,
			"order" => $presenter->page->order
		));
	}
    
	public function submitted($form)
	{
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		
		$values->url = Strings::webalize($values->name);
		
		$presenter->context->createPages()
			->find($presenter->page->id)
			->update(array(
				"name" => $values->name,
				"text" => $values->text,
				"access_rights" => $values->accessRights,
				"order" => $values->order,
				"url" => $values->url
			));
		
		$presenter->redirect("Documentation:", $values->url);
 	}
}
