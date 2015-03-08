<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Cron;

use Nette\Mail\SendmailMailer;
use Nette\Mail\Message;

/**
 * Čte a posílá emaily.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class MailReadJSON {

	const TYPE_EMAIL_NOTIFY = 1;
	const TYPE_EMAIL_OLD_USERS = 2;

	/** @var int emailType Typ posílaného emailu */
	private $emailType = self::TYPE_EMAIL_NOTIFY;

	public function __construct() {
		;
	}

	/**
	 * Nastavý typ odesílaného emailu.
	 * @param int $emailType
	 */
	public function setEmailType($emailType) {
		$this->emailType = $emailType;
	}

	/**
	 * Přečte JSON v url a vrátí ho.
	 * @param string $url Url, na které se nachází JSON
	 * @return JSON z této adresy
	 */
	public function readUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	/**
	 * Převede emaily z formátu JSON do pole.
	 * @param string $json JSON s emaily.
	 * @return array Pole s maily k odeslání.
	 */
	private function jsonEmailsToArray($json) {
		return json_decode($json, true);
	}

	/**
	 * Odešlě emaily.
	 * @param array $emails Emaily, co se mají poslat.
	 */
	private function send(array $emails) {
		$sendMailer = new SendmailMailer;

		foreach ($emails as $email) {
			$message = $this->createEmail($email);
			$sendMailer->send($message);
		}
	}

	/**
	 * Vytvoří a vrátí nový email.
	 * @param array $email Pole s hodnotami emailu.
	 * @return Message Nette\Mail\Message Email k odelsání.
	 * @throws Exception
	 */
	private function createEmail($email) {
		/* kontrola, zda email obsahuje nejdůležitější hodnoty */
		$checkKeys = array('from', 'to', 'subject', 'body');
		foreach ($checkKeys as $key) {
			if (!array_key_exists($key, $email)) {
				throw new Exception('Array $email must have key ' . $key);
			}
		}

		/* vytvoření emailu */
		$message = new Message();
		$message->setFrom($email['from']);
		$message->addTo($email['to']);
		$message->setSubject($email['subject']);

		if (isset($email['htmlBody']) && $email['htmlBody']) {
			$message->setHtmlBody($email['body']);
		} else {
			$message->setBody($email['body']);
		}

		return $message;
	}

	/**
	 * Pošle emaily, které se nacházejí na tomto url ve formátu JSON
	 * @param string $url Url, na které se nacházejí emaily které se mají
	 * poslat ve formátu JSON.
	 */
	public function sendEmails($url) {
		$json = $this->readUrl($url);
		$emails = $this->jsonEmailsToArray($json);
		$this->send($emails);
		$this->recordEmails($emails);
	}

	/**
	 * Zaznamená údaje o poslání emailů do databáze
	 * @param array $emails Poslané emaily.
	 */
	private function recordEmails($emails) {
		$database = DBConnection::getDatabase();
		$sel = $database->table('sended_emails');
		$sel->insert(array(
			'count' => count($emails),
			'type' => self::TYPE_EMAIL_NOTIFY
		));
	}

}
