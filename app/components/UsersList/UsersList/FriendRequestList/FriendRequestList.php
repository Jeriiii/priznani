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
		$this->template->friendRequests = $this->friendRequestDao->getAllToUser($this->loggedUserID);
		$this->renderTemplate(dirname(__FILE__) . '/' . 'friendRequestList.latte');
	}

	/**
	 * Přijmutí přátelství.
	 * @param int $id ID přátelství.
	 */
	public function handleAccept($id) {
		$this->friendRequestDao->accept($id);
		$this->getPresenter()->redirect(":Profil:Edit:friendRequests");
	}

	/**
	 * Odmítnutí přátelství.
	 * @param int $id ID přátelství.
	 */
	public function handleReject($id) {
		$this->friendRequestDao->reject($id);
		$this->getPresenter()->redirect("Profil:Edit: friendRequests");
	}

}
