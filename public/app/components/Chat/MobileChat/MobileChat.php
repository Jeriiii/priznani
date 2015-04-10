<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Komponenta celého webového chatu na POS
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class MobileChat extends PosChat {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/mobilechat.latte');
		$user = $this->getPresenter()->getUser();
		$template->logged = $user->isLoggedIn() && $user->getIdentity();
		$template->loggedUser = $this->loggedUser;
		$template->render();
	}

	/**
	 * Vytvoření komponenty zprostředkovávající seznam kontaktů
	 * @return \POSComponent\Chat\MobileContactList
	 */
	protected function createComponentMobileContactList() {
		return new MobileContactList($this->chatManager);
	}

}
