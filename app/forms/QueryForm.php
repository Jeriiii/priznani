<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class QueryForm extends BaseForm
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
		$this->addText('email', 'Váš e - mail: ')
				->addRule(Form::EMAIL, 'Nezadali jste platnou e-mailovou adresu');
		$this->addTextArea("content", "Text:")
				->addRule(Form::FILLED, "Zadejte prosím text zprávy");
		
		$this->addSubmit("submit", "Odeslat");
		$this->onSuccess[] = callback($this, 'submitted');

		return $this;
	}
	
	public function submitted(QueryForm $form)
	{
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		
		$values["create"] = new \Nette\DateTime;
		$id_click = $presenter->context->createForms_query()
						->insert($values);
		
		$this->sendMail();
		$this->registerNewSendForm(":Admin:Forms:formsQuery", NULL, $presenter, "Máte dotaz", $id_click);
		$presenter->flashMessage("Váš dotaz byl odeslán, odpovíme Vám co nejdříve", "info");
		$presenter->redirect("this");
 	}
	
	public function sendMail()
	{
		$this->sendMailAboutSendForm("Máte dotaz", $this->getPresenter());
	}
}