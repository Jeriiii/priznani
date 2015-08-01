<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	Nette\Utils\Strings as Strings;


class NewPageForm extends BaseForm
{
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		
		$presenter = $this->getPresenter();
		
		//form
		$this->addText('name', 'Jméno stránky:')
			->setRequired('Prosím vložte jméno stránky.');
		
		$this->addText('order', 'Pořadí stránky:', 5, 5)
			->setRequired('Prosím vložte pořadové číslo stránky.');
		
		$this->addTextArea('text', 'Text stránky:')
				->setAttribute("class","editor");
		
		$accessRights = array(
			"all" => "všichni",
			"admin" => "pouze administrátoři"
		);
		
		$this->addSelect("accessRights", "Kdo může stránku zobrazit", $accessRights);

		$this->addSubmit('send', 'Odeslat');
		
		$this->setDefaults(array(
			"order" => ($presenter->page->order + 1)
		));
		
		// call method signInFormSucceeded() on success
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted($form)
	{
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		
		$values->url = Strings::webalize($values->name);
		
		$presenter->context->createPages()
			->insert(array(
				"name" => $values->name,
				"text" => $values->text,
				"access_rights" => $values->accessRights,
				"order" => $values->order,
				"url" => $values->url
			));
		
		$presenter->redirect("Documentation:", $values->url);
 	}
}
