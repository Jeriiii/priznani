<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\Http\SessionSection;

class DatingRegistrationSecondForm extends DatingRegistrationBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/** @var \Nette\Http\SessionSection */
	private $regSession;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL, SessionSection $regSession = NULL) {
		parent::__construct($parent, $name);
		$this->userDao = $userDao;
		$this->regSession = $regSession;

		$this->addText('email', 'Email')
			->addRule(Form::FILLED, 'Email není vyplněn.')
			->addRule(Form::EMAIL, 'Vyplněný email není platného formátu.')
			->addRule(Form::MAX_LENGTH, 'Email je příliž dlouhý.', 50);
		$this->addText('user_name', 'Uživatelské jméno')
			->addRule(Form::FILLED, 'Uživatelské jméno není vyplněno')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Uživatelské jméno\" je 100 znaků.', 20);
		$this->addPassword('password', 'Heslo')
			->addRule(Form::FILLED, 'Heslo není vyplněno.')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 4)
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Heslo\" je 100 znaků.', 100);
		$this->addPassword('passwordVerify', 'Heslo znovu')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password'])
			->setAttribute('placeholder', 'pro kontrolu...');
		$this->addText('first_sentence', 'Úvodní věta')
			->addRule(Form::FILLED, 'Úvodní věta není vyplněna.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Úvodní věta\" je 100 znaků.', 100)
			->setAttribute('placeholder', 'max 100 znaků');
		$this->addTextArea('about_me', 'O mně', 40, 3)
			->addRule(Form::FILLED, 'O mně není vyplněno.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"O mě\" je 300 znaků.', 300)
			->setAttribute('placeholder', 'max 300 znaků');

		if (isset($regSession)) {
			$this->setDefaults(array(
				'email' => $regSession->email,
				'user_name' => $regSession->user_name,
				'first_sentence' => $regSession->first_sentence,
				'about_me' => $regSession->about_me,
			));
		}

		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, "uniqueUserName");
		$this->onValidate[] = callback($this, "uniqueEmail");
		$this->addSubmit('send', 'Do třetí části registrace')
			->setAttribute("class", "btn btn-main");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		$authenticator = $presenter->context->authenticator;
		$pass = $authenticator->calculateHash($values->password);

		$this->regSession->email = $values->email;
		$this->regSession->user_name = $values->user_name;
		$this->regSession->password = $values->password;
		$this->regSession->passwordHash = $pass;
		$this->regSession->first_sentence = $values->first_sentence;
		$this->regSession->about_me = $values->about_me;

		$presenter->redirect('DatingRegistration:ThirdRegForm');
	}

	/**
	 * Zkontroluje, zda je user_name unikátní
	 * @param Nette\Application\UI\Form $form
	 */
	public function uniqueUserName($form) {
		$values = $form->values;

		$user_name = $this->userDao->findByUserName($values->user_name);
		if ($user_name) {
			$form->addError('Toto jméno je již obsazeno.');
		}
	}

	/**
	 * Zkontroluje, zda je email unikátní
	 * @param Nette\Application\UI\Form $form
	 */
	public function uniqueEmail($form) {
		$values = $form->values;

		$email = $this->userDao->findByEmail($values->email);
		if ($email) {
			$form->addError('Tento mail již někdo používá.');
		}
	}

}
