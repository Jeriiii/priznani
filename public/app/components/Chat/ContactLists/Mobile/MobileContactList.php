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
class MobileContactList extends StandardContactList implements IContactList {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/mobile.latte');
		$user = $this->getPresenter()->getUser();
		$userIdentity = $user->getIdentity();
		$template->coder = $this->chatManager->getCoder();
		$template->contacts = $this->chatManager->getContacts($userIdentity->getId());
		$template->admin = $this->chatManager->getAdminContact();
		$template->username = $userIdentity->user_name;
		$template->contactList = $this;
		$template->render();
	}

}
