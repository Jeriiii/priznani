<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use POS\Model\UserDao;
use POS\Model\UserChangePasswordDao;
use Nette\Application\UI\Form;

class ChangePasswordForm extends BaseForm {

	/**
	 *
	 * @var \POS\Model\UserChangePasswordDao
	 */
	public $userChangePasswordDao;

	/**
	 *
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \Nette\Mail\IMailer
	 */
	public $mailer;
	public $userForPassChange;

	public function __construct(UserDao $userDao, UserChangePasswordDao $userChangePasswordDao, $userForPassChange, \Nette\Mail\IMailer $mailer, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->mailer = $mailer;
		$this->userChangePasswordDao = $userChangePasswordDao;
		$this->userDao = $userDao;
		$this->userForPassChange = $userForPassChange;
		$this->addPassword('password', 'Heslo:', 30, 200)
			->setRequired('Zvolte si heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 3);
		$this->addPassword('passwordVerify', 'Heslo pro kontrolu:', 30, 200)
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
		$this->addSubmit('send', 'Získat nové heslo');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ChangePasswordForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$password = $values->password;
		$hashPass = \Authenticator::calculateHash($password);
		$this->userDao->setPassword($this->userForPassChange->id, $hashPass);
		$this->userChangePasswordDao->deleteUserTickets($this->userForPassChange->id);
		$presenter->flashMessage("Heslo bylo změněno, nyní se můžete přihlásit", "info");
		$presenter->redirect("Sign:in");
	}

}
