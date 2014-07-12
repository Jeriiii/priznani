<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use POS\Model\UserDao;

class ForgottenPasswordForm extends BaseBootstrapForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \Nette\Mail\IMailer
	 */
	public $mailer;

	public function __construct(UserDao $userDao, \Nette\Mail\IMailer $mailer, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->mailer = $mailer;
		$this->userDao = $userDao;
		$this->addText('email', 'Email:', 30, 50);
		$this->addSubmit('send', 'Vygenerovat nové heslo')
			->setAttribute('class', 'btn-main medium');

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ForgottenPasswordForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$user = $this->userDao->findByEmail($values->email);
		if (empty($user)) {
			$form->addError(Html::el('div')->setText('Tento email nebyl nalezen.')->setClass('alert alert-danger'));
		} else {
			$password = Strings::random(15);
			$hashPass = \Authenticator::calculateHash($password);
			$this->userDao->setPassword($user->id, $hashPass);
			$this->sendMail($user->email, $password);
			$presenter->flashMessage("Heslo bylo odesláno emailem");
		}
	}

	public function sendMail($email, $password) {
		$mail = new Message;
		$mail->setFrom('info@nejlevnejsiwebstranky.cz')
			->addTo($email)
			->setSubject('Zapomenuté heslo')
			->setBody("Dobrý den,\nVaše přihlašovací údaje jsou:\ne-mail: " . $email . "\nheslo: " . $password . "\n\nS pozdravem\ntým nejlevnejsiwebstranky.cz");
		$this->mailer->send($mail);
	}

}
