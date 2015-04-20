<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Správce emailových oznámení
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace Notify;

use Nette\Database\Table\ActiveRow;
use NetteExt\DataCoder;

class EmailNotifies extends Emails {

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $setWeeklyLink;

	public function __construct($setWeeklyLink) {
		$this->setWeeklyLink = $setWeeklyLink;
	}

	/**
	 * Přidá aktivitu mezi upozornění k odeslání
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @param \Nette\Database\Table\ActiveRow $activity
	 */
	public function addActivity(ActiveRow $user, ActiveRow $activity) {
		$userNotify = $this->getUserNotify($user);
		$userNotify->addActivity($activity);
	}

	/**
	 * Přidá zprávu mezi upozornění k odeslání
	 * @param \Nette\Database\Table\ActiveRow $user
	 */
	public function addMessage(ActiveRow $user) {
		$userNotify = $this->getUserNotify($user);
		$userNotify->addMessage();
	}

	/**
	 * existuje již oznámení pro uživatele
	 * @return EmailNotify Upozornění pro uživatele.
	 */
	protected function getUserNotify($user) {
		if (!array_key_exists($user->id, $this->emailNotifies)) {
			$setWeeklyLink = $this->setWeeklyLink . '?id=' . DataCoder::encode($user->id);
			$this->emailNotifies[$user->id] = new EmailNotify($user, $setWeeklyLink);
		}

		return $this->emailNotifies[$user->id];
	}

}
