<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Utils\Strings,
	Nette\Mail\Message,
	Nette\Utils\Html;

class SignInForm extends BaseBootstrapForm {

	private $id;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//form
		
		$this->addText('email', 'E-mail:', 30, 200)
				->addRule(Form::FILLED, "Zadejte svůj email");
		
		$this->addPassword('password', 'Heslo:', 30, 200)
				->addRule(Form::FILLED, "Zadejte heslo");
		
		$this->addCheckbox('persistent', 'Pamatovat si mě na tomto počítači', 30, 200);
		
		$this->addSubmit('login', 'PŘIHLÁSIT SE')
				->setAttribute('class', 'btn-main medium');
		
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SignInForm $form) {
		$presenter = $this->getPresenter();
		try {
			$user = $presenter->getUser();
			$values = $form->getValues();
			if ($values->persistent) {
				$user->setExpiration('+30 days', FALSE);
			}
			$user->login($values->email, $values->password);
			//toto se provede při úspěšném zpracování přihlašovacího formuláře
			if (!empty($presenter->backlink)) {
				$presenter->flashMessage("Byl jste úspěšně přihlášen");
			}
			if ($presenter->user->isInRole("admin") || $presenter->user->isInRole("superadmin")) {
				$presenter->redirect('Admin:Forms:forms');
			} else {
				$presenter->redirect('Homepage:');
			}
		} catch (NS\AuthenticationException $e) {
			$form->addError(Html::el('div')->setText($e->getMessage())->setClass('alert alert-danger'));
		}
	}

}
