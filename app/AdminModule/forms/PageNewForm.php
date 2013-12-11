<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Utils\Strings as Strings;


class PageNewForm extends Form
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
		$this->addText("name", "Jméno", 30);
		$this->addTextarea('content', 'Text stránky:', 30)
			->getControlPrototype()->class('mceEditor');
		$this->addSubmit("submit", "Vytvořit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(PageNewForm $form)
	{
 	    $values = $form->values;
		$presenter = $this->presenter;
		
		$values->url = Strings::webalize($values->name);		
 	    $id_view = $presenter->context->createTexts()
					->insert($values);
		$order = $presenter->context->createPages()
					->order("order DESC")
					->fetch()
					->order;
		$presenter->context->createPages()
					->insert(array(
						"name" => $values->name,
						"url" => $values->url,
						"presenter" => "Page",
						"view" => "default",
						"id_view" => $id_view,
						"order" => ++$order
					));
	    $presenter->flashMessage('Stránka byla vytvořena');
 	    $presenter->redirect('Pages:pagesSort');
 	}
}
