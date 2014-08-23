<?php

/**
 * Formulář pro přihlášení uživatele
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Utils\Strings,
	Nette\Mail\Message,
	Nette\Utils\Html;

class SignInForm extends BaseForm {

	/**
	 * @var string Cesta zpět odkud uživatel přišel
	 */
	private $backlink;

	/**
	 * @var array Pole proměnných ve zpětném odkazu.
	 */
	private $backquery;

	public function __construct($banklink, $backquery, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->backlink = $banklink;
		$this->backquery =;

		$this->addText('email', 'E-mail:', 30, 200)
			->addRule(Form::FILLED, "Zadejte svůj email");

		$this->addPassword('password', 'Heslo:', 30, 200)
			->addRule(Form::FILLED, "Zadejte heslo");

		$this->addCheckbox('persistent', 'Pamatovat si mě na tomto počítači', 30, 200)
			->setDefaultValue(TRUE);

		$this->addSubmit('login', 'PŘIHLÁSIT SE')
			->setAttribute('class', 'btn-main medium');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SignInForm $form) {
		$presenter = $this->getPresenter();
		try {
			$user = $presenter->getUser();
			$values = $form->getValues();
			if ($values->persistent) {
				$presenter->getUser()->setExpiration('30 days', FALSE);
			} else {
				$presenter->getUser()->setExpiration('30 minutes', TRUE);
			}
			$user->login($values->email, $values->password);
			//toto se provede při úspěšném zpracování přihlašovacího formuláře
			//if (!empty($presenter->backlink)) {
			$presenter->flashMessage("Byl jste úspěšně přihlášen");
			//}
			if (!empty($this->backlink)) {
				$presenter->redirect($this->backlink);
			} elseif ($presenter->user->isInRole("admin") || $presenter->user->isInRole("superadmin")) {
				$presenter->redirect('Admin:Forms:forms');
			} else {
				$presenter->redirect('Homepage:');
			}
		} catch (NS\AuthenticationException $e) {
			$form->addError(Html::el('div')->setText($e->getMessage())->setClass('alert alert-danger'));
		}
	}

}
