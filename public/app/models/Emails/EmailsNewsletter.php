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

class EmailsNewsletter extends Emails {

	/**
	 * Přidá email do fronty
	 * @param ActiveRow $user
	 */
	public function addEmail($user) {
		$this->getUserNotify($user);
	}

	/**
	 * existuje již oznámení pro uživatele
	 * @return EmailNotify Upozornění pro uživatele.
	 */
	protected function getUserNotify($user) {
		if (!array_key_exists($user->id, $this->emailNotifies)) {
			$this->emailNotifies[$user->id] = new EmailNewsletter($user);
		}

		return $this->emailNotifies[$user->id];
	}

}
