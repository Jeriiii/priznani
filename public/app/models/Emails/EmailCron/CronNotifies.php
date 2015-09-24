<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

use POS\Model\ActivitiesDao;
use POS\Model\ChatMessagesDao;
use POS\Model\UserDao;
use POS\Model\ConfessionDao;

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

	/** @var \POS\Model\ConfessionDao */
	public $confessionDao;

	/** @var \Nette\Database\Table\Selection Uživatelé, kteří by měli dostat informační email. */
	public $usersForNewsletters;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $weeklyLink;

	public function __construct(ActivitiesDao $activitiesDao, ChatMessagesDao $chatMessagesDao, UserDao $userDao, ConfessionDao $confessionDao) {
		$this->activitiesDao = $activitiesDao;
		$this->chatMessagesDao = $chatMessagesDao;
		$this->usersForNewsletters = $userDao->getForNeswletters();
		$this->confessionDao = $confessionDao;
	}

	/**
	 * Nastaví odkaz pro tlačítko, přepínající na dodávání newsletterů pouze jednou za týden
	 * @param String $link odkaz
	 */
	public function setWeeklyLink($link) {
		$this->weeklyLink = $link;
	}

	/**
	 * Vrátí objekt pro práci či odeslání oznámení uživatelům emailem.
	 * @return EmailNotifies Objekt pro práci s oznámeními uživatelům.
	 */
	public function createEmails() {
		$activities = $this->activitiesDao->getNotViewedNotSendNotify($this->usersForNewsletters);
		$messages = $this->chatMessagesDao->getNotReadedNotSendNotify($this->usersForNewsletters);

		$emailNotifies = new EmailNotifies($this->weeklyLink);

		$emailNotifies->setConfessionText($this->getConfessionText());
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

	/**
	 * Vrátí text (náhodného) přiznání k zobrazení.
	 * @return string text přiznání
	 */
	private function getConfessionText() {
		$confessions = $this->confessionDao->getBestConfessions(60)->fetchPairs(ConfessionDao::COLUMN_ID, ConfessionDao::COLUMN_NOTE);
		return $confessions[array_rand($confessions)];
	}

}
