<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POS\Model\UserPropertyDao;
use POS\Model\UserDao;

/**
 * Formulář pro změnu hesla na profilu
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ProfileChangePasswordForm extends BaseForm {

	/**
	 * @var \POS\Model\CityDao
	 */
	public $userDao;

	public function __construct(UserDao $userDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;

		$this->addPassword('oldPassword', 'Staré heslo:', 30, 200)
			->setRequired('Musíte zadat staré heslo kvůli bezpečnosti');

		$this->addPassword('password', 'Nové heslo:', 30, 200)
			->addRule(Form::FILLED, 'Zvolte si nové heslo.')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 3);
		$this->addPassword('passwordVerify', 'Nové heslo pro kontrolu:', 30, 200)
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Nová hesla se neshodují', $this['password']);


		$this->addSubmit('changePasswordSend', 'Změnit heslo')
			->setAttribute("class", "btn-main medium button");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ProfileChangePasswordForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$user = $this->userDao->find($presenter->getUser()->getId());

		$oldPassword = \Authenticator::calculateHash($values->oldPassword);
		if ($user->password != $oldPassword) {
			$presenter->flashMessage('Špatné heslo. Potvrďte své oprávnění zadáním svého současného hesla.', 'error');
			$presenter->redirect('this');
		}
		unset($values->oldPassword);
		unset($values->passwordVerify);
		$values->password = \Authenticator::calculateHash($values->password);

		$this->userDao->update($presenter->getUser()->getId(), $values);

		$presenter->flashMessage('Vaše heslo bylo změněno.');
		$presenter->redirect('this');
	}

}
