<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use Nette\DateTime;
use POS\Ext\LastActive;

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
		$template->admin = $this->chatManager->getAdminContact();
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
		$lastActive = LastActive::format($user->last_active);
		return $lastActive;
	}

}
