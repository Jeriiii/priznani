<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class AuthorizatorForm extends Form
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
		
		$authorizator = $presenter->context->createAuthorizator_table()
			->fetch();
		
		$this->addCheckbox("galleries", "Galerie:");
		$this->addCheckbox("forms", "Forms:");
		$this->addCheckbox("accounts", "Správa účtů:");
		$this->addCheckbox("facebook", "Facebook:");
		$this->addCheckbox("files", "Soubory:");
		$this->addCheckbox("map", "Mapy:");
		$this->addCheckbox("google_analytics", "Google Analytics:");
		$this->addCheckbox("news", "Aktuality:");
		$this->addText("domain_name", "Název domény: http://");
		
		$this->setDefaults(array(
			"galleries" => $authorizator->galleries,
			"forms" => $authorizator->forms,
			"accounts" => $authorizator->accounts,
			"facebook" => $authorizator->facebook,
			"files" => $authorizator->files,
			"map" => $authorizator->map,
			"google_analytics" => $authorizator->google_analytics,
			"news" => $authorizator->news,
			"domain_name" => $authorizator->domain_name,
		));
		$this->addSubmit("submit", "Změnit");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(AuthorizatorForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$presenter->context->createAuthorizator_table()
				->update(array(
					"galleries" => $values->galleries,
					"forms" => $values->forms,
					"accounts" => $values->accounts,
					"facebook" => $values->facebook,
					"files" => $values->files,
					"map" => $values->map,
					"google_analytics" => $values->google_analytics,
					"news" => $values->news,
					"domain_name" => $values->domain_name,
				));
		$this->getPresenter()->flashMessage('Práva byla změněna.');
		$this->getPresenter()->redirect("this");
 	}
}
