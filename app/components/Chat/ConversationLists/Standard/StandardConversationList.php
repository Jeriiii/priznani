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

	/**
	 * Zpracování požadavku na načtení specifických konverzací
	 * @param type $limit limit konverzací
	 * @param type $offset offset konverzací
	 */
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

	/**
	 * Vrátí ze dvou id to správné uživatelské ID, které není přihlášený uživatel
	 * @param type $idSender první id
	 * @param type $idRecipient druhé id
	 * @return type
	 */
	public function getCorrectCodedId($idSender, $idRecipient) {
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		$coder = $this->chatManager->getCoder();
		if ($idSender == $loggedUserId) {
			return $coder->encodeData($idRecipient);
		} else {
			return $coder->encodeData($idSender);
		}
	}

	/**
	 * Vrátí uživatele s daným ID, který není přihlášený uživatel, aby mohl být vykreslen jeho profil
	 * @param int $id1 id prvního uživatele
	 * @param int $id2 id druhého uživatele
	 * @return Selection
	 */
	public function getCorrectUser($id1, $id2) {
		$loggedUserId = $this->getPresenter()->getUser()->getId();
		if ($id1 == $loggedUserId) {
			return $this->chatManager->getUserWithId($id2);
		} else {
			return $this->chatManager->getUserWithId($id1);
		}
	}

}
