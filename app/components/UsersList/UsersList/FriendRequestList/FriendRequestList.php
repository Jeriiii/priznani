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

	public function __construct($friendRequestDao, $loggedUserID, $parent, $name) {
		parent::__construct($parent, $name);
		$this->friendRequestDao = $friendRequestDao;
		$this->loggedUserID = $loggedUserID;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->renderTemplate(dirname(__FILE__) . '/' . 'friendRequestList.latte');
	}

	/**
	 * Přijmutí přátelství.
	 * @param int $id ID přátelství.
	 */
	public function handleAccept($id) {
		$this->friendRequestDao->accept($id);
		$this->redrawControl();
		//$this->getPresenter()->redirect(":Profil:Edit:friendRequests");
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
