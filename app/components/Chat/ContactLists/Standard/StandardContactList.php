<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Reprezentuje seznam kontaktů
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
		$template->logged = TRUE;
		$template->render();
	}

}
