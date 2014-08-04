<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;
use POS\Model\UserDao;

/**
 * Reprezentuje seznam kontaktů
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardContactList extends BaseProjectControl implements IContactList {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager) {
		$this->chatManager = $manager;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$user = $this->getPresenter()->getUser();
		$userIdentity = $user->getIdentity();
		if ($user->isLoggedIn() && $userIdentity) {
			$template->coder = $this->chatManager->getCoder();
			$template->contacts = $this->chatManager->getContacts($userIdentity->getId());
			$template->username = $userIdentity->user_name;
			$template->logged = TRUE;
		} else {
			$template->logged = FALSE;
		}
		$template->render();
	}

}
