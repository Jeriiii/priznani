<?php

/**
 * Formulář pro přihlášení uživatele
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Security as NS;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Nette\Application\Responses\JsonResponse;

class SignInForm extends BaseForm {

	const SECTION_BACKLINK_NAME = "backlink";

	/**
	 * @var boolean Cesta zpět odkud uživatel přišel
	 */
	private $backlink;

	public function __construct($banklink, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->backlink = $banklink;

		$this->addText('signEmail', 'E-mail:', 30, 200)
			->addRule(Form::FILLED, "Zadejte svůj email")
			->setAttribute('placeholder', 'Email...');

		$this->addPassword('signPassword', 'Heslo:', 30, 200)
			->addRule(Form::FILLED, "Zadejte heslo")
			->setAttribute('placeholder', 'Heslo...');

		$this->addCheckbox('signPersistent', 'Pamatovat si mě na tomto počítači', 30, 200)
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
			if ($values->signPersistent) {
				$presenter->getUser()->setExpiration('30 days', FALSE);
			} else {
				$presenter->getUser()->setExpiration('30 minutes', TRUE);
			}
			$user->login($values->signEmail, $values->signPassword);
			//toto se provede při úspěšném zpracování přihlašovacího formuláře

			$this->sendSuccessLogin();
		} catch (NS\AuthenticationException $e) {
			$this->sendErrorLogin($form, $e);
		}
	}

	/**
	 * Pošle uživateli zprávu o tom, že přihlášení bylo špatné.
	 */
	private function sendErrorLogin($form, NS\AuthenticationException $e) {
		$presenter = $this->getPresenter();
		$data = $this->getHttpData();

		if (array_key_exists("mobile", $data)) {
			$sendData["success"] = 0;
			$sendData['errorMessage'] = $e->getMessage();

			$json = new JsonResponse($sendData, "application/json; charset=utf-8");
			$presenter->sendResponse($json);
		} else {
			$form->addError($e->getMessage());
		}
	}

	/**
	 * Pošle uživateli, že byl úspěšně přihlášen - reaguje na json.
	 */
	private function sendSuccessLogin() {
		$presenter = $this->getPresenter();
		$data = $this->getHttpData();

		if (array_key_exists("mobile", $data)) {
			$sendData["success"] = 1;

			$json = new JsonResponse($sendData, "application/json; charset=utf-8");
			$presenter->sendResponse($json);
		} else {
			$presenter->flashMessage("Byl jste úspěšně přihlášen");

			if ($this->backlink == TRUE) {
				$this->redirectBacklink();
			} elseif ($presenter->user->isInRole("admin") || $presenter->user->isInRole("superadmin")) {
				$presenter->redirect('Admin:Forms:forms');
			} else {
				$presenter->redirect(':OnePage:');
			}
		}
	}

	/**
	 * Přesměruje uživatele na dříve prohlíženou stránku
	 */
	private function redirectBacklink() {
		/* nastaven backlink */
		$backlinkSession = $this->presenter->getSession(self::SECTION_BACKLINK_NAME);
		$link = $backlinkSession->link;
		$query = $backlinkSession->query;
		$backlinkSession->remove();
		$presenter = $this->presenter;
		if (!empty($link)) {
			$presenter->redirect($link, $query);
		} else {
			$presenter->redirect(':OnePage:');
		}
	}

}
