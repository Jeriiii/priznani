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
class StandardConversationList extends BaseChatComponent implements IContactList {

	private $handledConversations = FALSE;

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');


		$template->userfinder = $this;


		$userId = $this->getPresenter()->getUser()->getId();
		if ($this->handledConversations) {
			$template->conversations = $this->chatManager->getConversations($userId);
		} else {
			$template->conversations = $this->handledConversations;
		}
		$template->userId = $userId;


		$template->loadLink = $this->link('load!');

		$template->render();
	}

	public function handleLoad($limit, $offset) {
		if ($this->getPresenter()->isAjax()) {
			$userId = $this->getPresenter()->getUser()->getId();
			$this->template->load = TRUE; //jen pro ifset
			$this->handledConversations = $this->chatManager->getConversations($userId, $limit, $offset, TRUE);
			$this->redrawControl('conversations');
		}
	}

	/**
	 * Vrátí přihlašovací jméno, které souvisí s uživatelem, s nímž si píše
	 * přihlášený uživatel
	 * @param int $idSender id odesílatele
	 * @param int $idRecipient
	 * @return string jméno příjemce
	 */
	public function getCorrectUsername($idSender, $idRecipient) {
		$session = $this->getPresenter()->getSession(StandardCommunicator::USERNAMES_SESSION_NAME);
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		if ($idSender == $loggedUserId) {
			return $this->chatManager->getUsername($idRecipient, $session);
		} else {
			return $this->chatManager->getUsername($idSender, $session);
		}
	}

	public function getCorrectCodedId($idSender, $idRecipient) {
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		$coder = $this->chatManager->getCoder();
		if ($idSender == $loggedUserId) {
			return $coder->encodeData($idRecipient);
		} else {
			return $coder->encodeData($idSender);
		}
	}

}
