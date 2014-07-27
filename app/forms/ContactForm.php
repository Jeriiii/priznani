<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;
use POS\Model\ContactDao;

class ContactForm extends BaseForm {

	/**
	 * @var \POS\Model\ContactDao
	 * @inject
	 */
	public $contactDao;
	public $presenter;

	public function __construct(ContactDao $contactDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->contactDao = $contactDao;
		$this->presenter = $this->getPresenter();

		if ($this->presenter->user->isLoggedIn()) {
			$email = $this->presenter->user->identity->email;

			$this->addText('email', 'E-mail')
				->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail')
				->setDefaultValue($email);
		} else {
			$this->addText('email', 'E-mail')
				->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail');
		}
		$this->addText('phone', 'Telefon')
			->setRequired('Zadejte telefonní číslo');

		$this->addTextArea('text', 'Zpráva')
			->setRequired('Vyplňte text zprávy')
			->addRule(Form::MAX_LENGTH, 'Zpráva může být dlouhá maximálně %d znaků', 500);

		$this->addSubmit("submit", "Odeslat");

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
