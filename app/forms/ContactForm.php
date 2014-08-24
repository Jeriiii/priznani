<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\ContactDao;

class ContactForm extends BaseForm {

	/**
	 * @var \POS\Model\ContactDao
	 */
	public $contactDao;

	public function __construct(ContactDao $contactDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->contactDao = $contactDao;

		$this->addText('email', 'E-mail');

		$this->addText('phone', 'Telefon')
			->addConditionOn($this["email"], ~Form::FILLED)
			->setRequired('Zadejte telefonní číslo');

		$this["email"]
			->addCondition(Form::FILLED)
			->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail')
			->addConditionOn($this["phone"], ~Form::FILLED)
			->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail');

		$this->addTextArea('text', 'Zpráva')
			->setRequired('Vyplňte text zprávy')
			->addRule(Form::MAX_LENGTH, 'Zpráva může být dlouhá maximálně %d znaků', 500);

		$user = $this->presenter->user;
		if ($user->isLoggedIn()) {
			$email = $user->identity->email;
			$this->setDefaults(array(
				"email" => $email
			));
		}

		$this->addSubmit("submit", "Odeslat");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ContactForm $form) {
		$values = $form->getValues();

		$userID = $this->presenter->user->id;
		$this->contactDao->addNewContact($userID, $values->email, $values->phone, $values->text);
		$this->presenter->flashMessage('Vaše zpráva byla odeslána');
		$this->presenter->redirect('OnePage:');
	}

}
