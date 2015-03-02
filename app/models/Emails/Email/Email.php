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
	 * @var bool HTML body
	 */
	public $htmlBody;

	/**
	 * @var string Cesta k obrázkům, co se mají připojit do mailu. Dá se na ně pak odkazovat z HTML.
	 */
	public $imgsBasePath;

	/**
	 * Emailová adresa odesílatele (stránky)
	 */
	const EMAIL_ADDRESS_SENDER = "info@priznaniosexu.cz";

	public function __construct($user, $htmlBody = false, $imgsBasePath = null) {
		$this->user = $user;
		$this->htmlBody = $htmlBody;
		$this->imgsBasePath = $imgsBasePath;
	}

	/**
	 * Pošle email uživateli.
	 * @param IMailer $mailer
	 */
	public function sendEmail(IMailer $mailer) {
		$mail = new Message;
		$mail->setFrom(self::EMAIL_ADDRESS_SENDER);

		$email = $this->getEmailAddress();
		$subject = $this->getEmailSubject();
		$body = $this->getEmailBody();

		$mail->addTo($email);
		$mail->setSubject($subject);

		if ($this->htmlBody) {
			$mail->setHtmlBody($body, $this->imgsBasePath);
		} else {
			$mail->setBody($body);
		}

		$mailer->send($mail);
	}

	/**
	 * Vrátí email, který se má poslat.
	 * @return array Email, který se má poslat.
	 */
	public function getEmail() {
		$subject = $this->getEmailSubject();
		$body = $this->getEmailBody();
		$from = self::EMAIL_ADDRESS_SENDER;
		$to = $this->getEmailAddress();
		$htmlBody = false;

		if ($this->htmlBody) {
			$htmlBody = true;
		}

		$email = array(
			'from' => $from,
			'to' => $to,
			'body' => $body,
			'subject' => $subject,
			'htmlBody' => $htmlBody
		);

		return $email;
	}

	/**
	 * Vrátí emailovou adresu příjemce
	 * @return string Emailová adresa
	 */
	public function getEmailAddress() {
		return $this->user->email;
	}

}
