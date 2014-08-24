<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Nette\Utils\Html;
use POS\Model\UserDao;
use POS\Model\UserChangePasswordDao;

class ForgottenPasswordForm extends BaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 *
	 * @var \POS\Model\UserChangePasswordDao
	 */
	public $userChangePasswordDao;

	/**
	 * @var \Nette\Mail\IMailer
	 */
	public $mailer;

	public function __construct(UserChangePasswordDao $userChangePasswordDao, UserDao $userDao, \Nette\Mail\IMailer $mailer, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$email = $this->getPresenter()->getParameter('email');

		$this->mailer = $mailer;
		$this->userDao = $userDao;
		$this->userChangePasswordDao = $userChangePasswordDao;
		$this->addText('email', 'Email:', 30, 50);
		$this->addSubmit('send', 'Změnit heslo');

		if (!empty($email)) {
			$this->setDefaults(array("email" => $email));
		}

		$this->setBootstrapRender();
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
			$ticket = $this->generateTicket($user->id);
			$this->userChangePasswordDao->deleteUserTickets($user->id);
			$this->userChangePasswordDao->addNewTicket($user->id, $ticket);
			$this->sendMail($user->email, $ticket);
			$presenter->flashMessage("Byl vám odeslán email s dalšími instrukcemi");
		}
	}

	public function sendMail($email, $ticket) {
		$link = $this->getPresenter()->link("//:Sign:changePassword", array("ticket" => $ticket));

		$mail = new Message;
		$mail->setFrom('neodepisuj@priznaniosexu.cz')
			->addTo($email)
			->setSubject('Zapomenuté heslo')
			->setBody("Dobrý den,\nPro získání nového hesla pokračujte na:\n" . $link . "\n\nS pozdravem\ntým priznaniosexu.cz");
		$this->mailer->send($mail);
	}

	/**
	 * Vygeneruje náhodný string a připojí id uživatele, to celé tvoří ticket
	 * @param int $userID Id uživatele
	 * @return String
	 */
	protected function generateTicket($userID) {
		$stringPart = Strings::random(19);
		$ticket = $stringPart . $userID;

		return $ticket;
	}

}
