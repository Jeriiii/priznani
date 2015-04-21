<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

/**
 * Připravuje emaily oznámení pro určité použití cronem
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CronNotifies extends CronEmails {

	/** @var \POS\Model\ActivitiesDao */
	public $activitiesDao;

	/** @var \POS\Model\ChatMessagesDao */
	public $chatMessagesDao;

	/** @var \Nette\Database\Table\Selection Uživatelé, kteří by měli dostat informační email. */
	public $usersForNewsletters;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;

	public function __construct($activitiesDao, $chatMessagesDao, $userDao, $setWeeklyLink) {
		$this->activitiesDao = $activitiesDao;
		$this->chatMessagesDao = $chatMessagesDao;
		$this->usersForNewsletters = $userDao->getForNeswletters();
		$this->setWeeklyLink = $setWeeklyLink;
	}

	/**
	 * Vrátí objekt pro práci či odeslání oznámení uživatelům emailem.
	 * @return EmailNotifies Objekt pro práci s oznámeními uživatelům.
	 */
	public function createEmails() {
		$activities = $this->activitiesDao->getNotViewedNotSendNotify($this->usersForNewsletters);
		$messages = $this->chatMessagesDao->getNotReadedNotSendNotify($this->usersForNewsletters);

		$emailNotifies = new EmailNotifies($this->setWeeklyLink);
		/* upozornění na aktivity */
		foreach ($activities as $activity) {
			$emailNotifies->addActivity($activity->event_owner, $activity);
		}

		/* upozornění na zprávy */
		foreach ($messages as $message) {
			if (isset($message->id_recipient)) { //konverzace jsou null a tak se neposílají
				$emailNotifies->addMessage($message->recipient);
			}
		}

		return $emailNotifies;
	}

	/**
	 * Oznámí, že všechny emaily co mohli být do teď odeslány, opravdu odeslány jsou
	 */
	public function markEmailsLikeSended() {
		$this->activitiesDao->updateSendNotify($this->usersForNewsletters);
		$this->chatMessagesDao->updateSendNotify($this->usersForNewsletters);
	}

}
