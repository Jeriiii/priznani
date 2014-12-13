<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use Nette\DateTime;

/**
 * Reprezentuje seznam kontaktů a vykresluje jej
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardContactList extends BaseChatComponent implements IContactList {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$user = $this->getPresenter()->getUser();
		$userIdentity = $user->getIdentity();
		$template->coder = $this->chatManager->getCoder();
		$template->contacts = $this->chatManager->getContacts($userIdentity->getId());
		$template->username = $userIdentity->user_name;
		$template->contactList = $this;
		$template->render();
	}

	/**
	 * Vrátí čas poslední aktivity
	 * @param ActiveRow $user
	 * @return string
	 */
	public function getLastActivity($user) {
		$now = new DateTime();
		$lastActive = $now->diff(new DateTime($user->last_active));
		if ($lastActive->m > 0) {
			$lastActive = " ";
		} else if ($lastActive->d > 0) {
			$lastActive = "(" . $lastActive->d . " d" . ")";
		} else if ($lastActive->h > 0) {
			$lastActive = "(" . $lastActive->h . " h" . ")";
		} else {
			$lastActive = "(" . $lastActive->m . " m" . ")";
		}
		return $lastActive;
	}

}
