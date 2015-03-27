<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 2.3.2015
 */

namespace Notify;

/**
 * Připravuje emaily pro bývalé uživatele na určité použití cronem
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CronForOldUsers extends CronEmails {

	/** @var \POS\Model\OldUserDao */
	public $oldUserDao;

	/** @var int $limit Limit, s kolika emaily se v jednom kroku pracuje. */
	private $limit = 100;

	public function __construct($oldUserDao) {
		$this->oldUserDao = $oldUserDao;
	}

	/**
	 * Vrátí objekt pro práci či odeslání oznámení uživatelům emailem.
	 * @return EmailNotifies Objekt pro práci s oznámeními uživatelům.
	 */
	public function createEmails() {
		$users = $this->oldUserDao->getNoNotify($this->limit);
		$emailsForOldUsers = new EmailsForOldUsers($this->mailer);

		/* upozornění na aktivity */
		foreach ($users as $user) {
			$emailsForOldUsers->addEmail($user);
		}

		return $emailsForOldUsers;
	}

	/**
	 * Oznámí, že všechny emaily co mohli být do teď odeslány, opravdu odeslány jsou
	 */
	public function markEmailsLikeSended() {
		$users = $this->oldUserDao->getNoNotify($this->limit);
		$this->oldUserDao->updateLimitNotify($users);
	}

}
