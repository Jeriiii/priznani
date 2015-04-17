<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Reprezentuje seznam konverzací a vykresluje jej
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class MobileConversationList extends StandardConversationList implements IContactList {

	private $handledConversations = FALSE;

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/mobile.latte');


		$template->userfinder = $this;


		$userId = $this->getPresenter()->getUser()->getId();
		if ($this->handledConversations) {
			$template->conversations = $this->chatManager->getConversations($userId);
		} else {
			$template->conversations = $this->handledConversations;
		}
		$template->userId = $userId;

		$template->render();
	}

}
