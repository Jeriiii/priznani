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

	/** @var \Nette\Database\Table\Selection Aktivity, které se mají odeslat v emailu. */
	private $activities;

	/** @var \Nette\Database\Table\Selection Zprávy, které se mají odeslat v emailu */
	private $messages;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;

	public function __construct($activitiesDao, $chatMessagesDao, $setWeeklyLink) {
		$this->activitiesDao = $activitiesDao;
		$this->chatMessagesDao = $chatMessagesDao;
		$this->setWeeklyLink = $setWeeklyLink;
	}

	/**
	 * Vrátí objekt pro práci či odeslání oznámení uživatelům emailem.
	 * @return EmailNotifies Objekt pro práci s oznámeními uživatelům.
	 */
	public function createEmails() {
		$activities = $this->activitiesDao->getNotViewedNotSendNotify();
		$messages = $this->chatMessagesDao->getNotReadedNotSendNotify();

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

		$this->activities = $activities;
		$this->messages = $messages;

		return $emailNotifies;
	}

	/**
	 * Oznámí, že všechny emaily co mohli být do teď odeslány, opravdu odeslány jsou
	 */
	public function markEmailsLikeSended($userDao) {
		$this->activitiesDao->updateSendNotify($this->activities);
		$this->chatMessagesDao->updateSendNotify($this->messages);
		$userDao->setNotifyAsSended();
	}

}
