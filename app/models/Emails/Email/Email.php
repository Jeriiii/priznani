<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Oznámení, které se mají odeslat emailem.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace Notify;

use Nette\Database\Table\ActiveRow;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;

abstract class Email extends Object implements IEmail {

	/**
	 * @var ActiveRow Uživatel
	 */
	public $user;

	/**
	 * Emailová adresa odesílatele (stránky)
	 */
	const EMAIL_ADDRESS_SENDER = "info@priznaniosexu.cz";

	public function __construct($user) {
		$this->user = $user;
	}

	public function sendEmail(IMailer $mailer) {
		$mail = new Message;
		$mail->setFrom(self::EMAIL_ADDRESS_SENDER);

		$email = $this->getEmailAddress();
		$subject = $this->getEmailSubject();
		$body = $this->getEmailBody();

		$mail->addTo($email);
		$mail->setSubject($subject);
		$mail->setBody($body);

		$mailer->send($mail);
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailAddress() {
		return $this->user->email;
	}

}
