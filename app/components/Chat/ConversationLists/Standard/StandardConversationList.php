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
class StandardConversationList extends BaseChatComponent implements IContactList {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$userId = $this->getPresenter()->getUser()->getId();
		$template->logged = $this->getPresenter()->getUser()->isLoggedIn();
		$template->coder = $this->chatManager->getCoder();
		$template->conversations = $this->chatManager->getConversations($userId);
		$template->render();
	}

}
