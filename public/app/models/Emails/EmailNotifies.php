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

	/** Text přiznání, které se má zobrazovat v emailech */
	private $confessionText = NULL;

	/** @var array uživatelé, co se mají ukázat v emailu. Pole, jehož hodnoty jsou cesty k profilovým fotkám */
	private $usersToShow = array();

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
	 * Nastaví mailům text přiznání, které zobrazují v patičce
	 * @param string $text
	 */
	public function setConfessionText($text) {
		$this->confessionText = $text;
	}

	/**
	 * Přidá fotku uživatele, který se má zobrazit.
	 * @param string $photoUrl absolutní url fotky uživatele
	 */
	public function addUserPhotoToShow($photoUrl) {
		$this->usersToShow[] = $photoUrl;
	}

	/**
	 * existuje již oznámení pro uživatele
	 * @return EmailNotify Upozornění pro uživatele.
	 */
	protected function getUserNotify($user) {
		if (!array_key_exists($user->id, $this->emailNotifies)) {
			$setWeeklyLink = $this->setWeeklyLink . '?id=' . DataCoder::encode($user->id);
			$this->emailNotifies[$user->id] = new EmailNotify($user, $setWeeklyLink, $this->confessionText, $this->usersToShow);
		}

		return $this->emailNotifies[$user->id];
	}

}
