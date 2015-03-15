<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserCategoryDao;

class DatingEditSecondForm extends BaseForm {

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\UserPropertyDao */
	public $userPropertyDao;

	/** @var \Nette\Database\Table\ActiveRow */
	private $user;

	/**
	 * @var \POS\Model\UserCategoryDao
	 */
	public $userCategoryDao;

	public function __construct(UserCategoryDao $userCategoryDao, UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userPropertyDao = $userPropertyDao;
		$presenter = $this->getPresenter();
		$userID = $presenter->getUser()->getId();
		$this->userCategoryDao = $userCategoryDao;

		$this->user = $userDao->find($userID);

		$this->addGroup('Identifikační údaje');

		$this->addText('email', 'Email')
			->addRule(Form::FILLED, 'Email není vyplněn.')
			->addRule(Form::EMAIL, 'Vyplněný email není platného formátu.')
			->addRule(Form::MAX_LENGTH, 'Email je příliž dlouhý.', 50)
			->setDisabled();
		$this->addText('user_name', 'Uživatelské jméno')
			->addRule(Form::FILLED, 'Uživatelské jméno není vyplněno')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Uživatelské jméno\" je 100 znaků.', 20)
			->setDisabled();
		$this->addText('first_sentence', 'Úvodní věta (max 100 znaků)')
			->addRule(Form::FILLED, 'Úvodní věta není vyplněna.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Úvodní věta\" je 100 znaků.', 100);
		$this->addTextArea('about_me', 'O mě (max 300 znaků)', 40, 3)
			->addRule(Form::FILLED, 'O mě není vyplněno.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"O mě\" je 300 znaků.', 300);

		$this->setDefaults(array(
			"email" => $this->user->email,
			"user_name" => $this->user->user_name,
			"first_sentence" => $this->user->property->first_sentence,
			"about_me" => $this->user->property->about_me
		));

		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn-main medium button");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();

		unset($values["email"]);
		unset($values["user_name"]);
		$this->userPropertyDao->update($this->user->propertyID, $values);

		$this->userPropertyDao->updatePreferencesID($this->user->property, $this->userCategoryDao);

		$presenter->calculateLoggedUser();
		$presenter->flashMessage('Změna identifikačních údajů byla úspěšná');
		$presenter->redirect("this");
	}

}