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

use IAjaxBox;

class FriendRequestList extends UsersList implements \IAjaxBox {

	/** @var \POS\Model\FriendRequestDao */
	private $friendRequestDao;

	/** @var int ID přihlášeného uživatele */
	private $loggedUserID;

	/** @var int Posun příspěvků při rolování */
	private $offset = 0;

	/** @var int Limit na jedno načtení */
	private $limit;

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
	 * Tuto metodu zavolejte ze metody render. Nastavý data, který se mají vrátit v ajaxovém i normálním
	 * požadavku v závislosti na předaném offsetu (posunu od shora).
	 * @param int $offset Offset předaný metodou handleGetMoreData. Při vyrendrování komponenty je nula.
	 * @param int $limit Limit na jedno načtení
	 */
	public function handleGetMoreData($offset, $limit) {
		$this->offset = $offset;
		$this->limit = $limit;
		$this->setData($this->offset);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl("requests");
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$friendRequests = $this->friendRequestDao->getAllToUser($this->loggedUserID, $this->limit, $offset);
		$this->template->friendRequests = $friendRequests;
	}

}
