<?php

/**
 * Formulář pro přihlášení uživatele
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Security as NS;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;

class SignInForm extends BaseForm {

	/**
	 * @var boolean Cesta zpět odkud uživatel přišel
	 */
	private $backlink;

	public function __construct($banklink, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->backlink = $banklink;

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
			if ($this->backlink == TRUE) {
				$this->redirectBacklink();
			} elseif ($presenter->user->isInRole("admin") || $presenter->user->isInRole("superadmin")) {
				$presenter->redirect('Admin:Forms:forms');
			} else {
				$presenter->redirect('Homepage:');
			}
		} catch (NS\AuthenticationException $e) {
			$form->addError(Html::el('div')->setText($e->getMessage())->setClass('alert alert-danger'));
		}
	}

	/**
	 * Přesměruje uživatele na dříve prohlíženou stránku
	 */
	private function redirectBacklink() {
		/* nastaven backlink */
		$backlinkSession = $this->presenter->getSession('backlink');
		$link = $backlinkSession->link;
		$query = $backlinkSession->query;
		$backlinkSession->remove();
		$presenter = $this->presenter;
		if (!empty($link)) {
			$presenter->redirect($link, $query);
		} else {
			$presenter->redirect('Homepage:');
		}
	}

}
