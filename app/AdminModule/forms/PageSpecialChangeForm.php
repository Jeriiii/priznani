<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
    Nette\ComponentModel\IContainer;


class PageSpecialChangeForm extends Form
{
	private $id;
	
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
		$this->id = $presenter->id_page;
		$page = $presenter->context->createPages()
				->where('id', $this->id)
				->fetch();
		
		$this->addGroup('Základní nastavení');
		$this->addText("name", "Jméno stránky", 30)
			->setDefaultValue($page->name);
		$this->addSubmit("submit", "Změnit");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
    public function submitted(PageSpecialChangeForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		$presenter->context->createPages()
			->where("id",  $this->id)
			->update($values);
		$presenter->flashMessage('Stránka byla změněna');
		$presenter->redirect('Pages:pagesSort');
 	}
}
