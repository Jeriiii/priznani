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
	/* počáteční limit */

	const LIMIT_OF_NEW_CONVERSATIONS = 10;

	/* aktuální offset */

	private $offset = 0;

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/mobile.latte');

		$template->userfinder = $this;
		$userId = $this->getPresenter()->getUser()->getId();

		$template->conversations = $this->chatManager->getConversations($userId, self::LIMIT_OF_NEW_CONVERSATIONS, $this->offset, TRUE);
		$template->userId = $userId;

		$template->render();
	}

	/**
	 * Zpracování požadavku na načtení specifických konverzací
	 * @param type $limit limit konverzací
	 * @param type $offset offset konverzací
	 */
	public function handleLoad($limit = self::LIMIT_OF_NEW_CONVERSATIONS, $offset = 0) {
		if ($this->getPresenter()->isAjax()) {
			$this->offset = $offset;
			$this->redrawControl('conversations');
		}
	}

}
