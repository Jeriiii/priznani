<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Používá se pro prohlížení žádostí o přátelství a přidávání lidí do přátel.
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList;

class FriendRequestList extends UsersList {

	/** @var \POS\Model\FriendRequestDao */
	private $friendRequestDao;

	/** @var int ID přihlášeného uživatele */
	private $loggedUserID;

	/* info o tom, zda se obsah donačítá (když true, obsah je zobrazen vždy všechen) */
	private $listAll = FALSE;

	public function __construct($friendRequestDao, $loggedUserID, $parent, $name, $listAll = FALSE) {
		parent::__construct($parent, $name);
		$this->friendRequestDao = $friendRequestDao;
		$this->loggedUserID = $loggedUserID;
		$this->listAll = $listAll;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		parent::render();
		if ($this->listAll) {
			$this->template->friendRequests = $this->friendRequestDao->getAllToUser($this->loggedUserID);
		}
		if ($this->getEnvironment()->isMobile()) {
			$this->renderTemplate(dirname(__FILE__) . '/' . 'mobileFriendRequestList.latte');
		} else {
			$this->renderTemplate(dirname(__FILE__) . '/' . 'friendRequestList.latte');
		}
	}

	/**
	 * Přijmutí přátelství.
	 * @param int $id ID přátelství.
	 */
	public function handleAccept($id) {
		$this->friendRequestDao->accept($id);
		if ($this->presenter->isAjax()) {
			$this->redrawControl();
		} else {
			$this->presenter->redirect("this");
		}
	}

	/**
	 * Odmítnutí přátelství.
	 * @param int $id ID přátelství.
	 */
	public function handleReject($id) {
		$this->friendRequestDao->reject($id);
		$this->redrawControl();
		//$this->getPresenter()->redirect("Profil:Edit: friendRequests");
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$friendRequests = $this->friendRequestDao->getAllToUser($this->loggedUserID, $this->limit, $offset);
		$this->template->friendRequests = $friendRequests;
	}

	public function getSnippetName() {
		return "requests";
	}

}
