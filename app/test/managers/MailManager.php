<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use \Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * Sends mail to the directory instead off using SMTP server
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class MailManager implements IMailer {

	/**
	 *
	 * @var string url to folder where will emails be stored
	 */
	private $mailDir;

	/**
	 * Init of overriding mailer
	 * @param type $mailDir dir for emails
	 */
	public function __construct($mailDir) {
		$this->mailDir = $mailDir;
	}

	/**
	 * Method from IMailer - overriding real sending messages
	 * @param \Nette\Mail\Message $mail
	 */
	public function send(Message $mail) {
		$message = $mail->generateMessage();
		dump($message);
		$filepath = $this->mailDir . '/' . time() . '-' . rand(0, 1000) . '.txt';
		file_put_contents($filepath, $message);
	}

}
