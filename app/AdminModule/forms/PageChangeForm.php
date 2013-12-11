<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
    Nette\ComponentModel\IContainer,
	Nette\Utils\Strings as Strings;


class PageChangeForm extends Form
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
		$page = $presenter->context->createTexts()
				->where('id', $this->id)
				->fetch();
		
		$this->addGroup('Základní nastavení');
		$this->addText("name", "Jméno", 30)
			->setDefaultValue($page->name);
		
		if ($presenter->getSession('allow')->map)
		$this->addCheckbox("map", "Zobrazit mapu")
			->setDefaultValue($page->map);
		
		$this->addTextarea('content', 'Text stránky:', 30, 20)
			->setDefaultValue($page->content)
			->getControlPrototype()->class('mceEditor');
		$this->addGroup('Pokročilé nastavení');
		$this->addCheckbox("visibility_menu", "Zobrazit stránku v menu")
			->setDefaultValue($page->visibility_menu);
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
    public function submitted(PageChangeForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		
		$values->url = Strings::webalize($values->name);
		$presenter->context->createTexts()
			->where("id",  $this->id)
			->update($values);
		
		$presenter->context->createPages()
			->where("id_view", $this->id)
			->update(array(
				"name" => $values->name,
				"url" => $values->url,
				"visibility_menu" => $values->visibility_menu
			));
		
		$presenter->flashMessage('Stránka byla změněna');
		$presenter->redirect('Pages:changePage', $this->id);
 	}
}
