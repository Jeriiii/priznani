<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message;


class Form3NewForm extends BaseForm
{
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
//		//graphics
//		$renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = NULL;
		//form
		$this->addText("name", "Jméno:", 30, 200)
			->addRule(Form::FILLED,"Zadejte prosím své jméno.");
		$this->addText("mail", "E-Mail:", 30, 200)
			->addRule(Form::FILLED, "Zadejte prosím svůj email.")
			->addRule(Form::EMAIL, "Email není zadán správně.");
		$this->addText("phone", "Telefon:", 30, 50);
		$this->addText("persons", "Počet osob:", 10, 3)
			->addRule(Form::INTEGER, "Počet osob musí být číslo");
		$this->addTextarea('note', 'Text stránky:', 30)
			->getControlPrototype()->class('mceEditor');
		$this->addSubmit("submit", "Odeslat");
		$this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}
    
	public function submitted(Form3NewForm $form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$id_page = $presenter->id_page;
		$values["id_form"] = $presenter->id_form;
		$values["create"] = new \Nette\DateTime;
		$id_click = $presenter->context->createForms3()
			->insert($values);
		$this->sendMail();
		$this->registerNewSendForm("standart", $presenter->id_form, $presenter, NULL, $id_click);
		$presenter->flashMessage('Formulář byl odeslán');
		$presenter->redirect('this');
 	}
	
	public function sendMail()
	{
		$presenter = $this->getPresenter();
		$form_name = $presenter->context->createForms()
				->find($presenter->id_form)
				->fetch()
				->name;
		
		$this->sendMailAboutSendForm($form_name, $presenter);
	}
}
