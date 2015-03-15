<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use \Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Finder;

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
	 * Returns instance of email manager
	 * @return EmailManager
	 */
	public static function getInstance() {
		return $GLOBALS['container']->getByType(__CLASS__);
	}

	/**
	 * Method from IMailer - overriding real sending messages
	 * @param \Nette\Mail\Message $mail
	 */
	public function send(Message $mail) {
		$message = $mail->generateMessage();
		$filepath = $this->mailDir . '/' . time() . '-' . rand(0, 1000) . '.txt';
		file_put_contents($filepath, $message);
	}

	/**
	 * Returns last receive email
	 * @return string|NULL text of email or null if no email left
	 */
	public function getLastEmail() {
		$files = Finder::find('*.txt')->in($this->mailDir);
		$filesArray = iterator_to_array($files);
		ksort($filesArray);
		if ($filesArray && !empty($filesArray)) {
			$file = array_shift($filesArray);
			$path = $file->getPathname();
			$text = file_get_contents($path);
			unlink($path); //delete file when readed
			return $text;
		}
		return NULL;
	}

	/**
	 * Deletes all emails what left in email folder
	 */
	public function clearEmails() {
		$files = Finder::find('*.txt')->in($this->mailDir);
		foreach ($files as $file) {
			unlink($file);
		}
	}

}
