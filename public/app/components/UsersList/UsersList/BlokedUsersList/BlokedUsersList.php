<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Zobrazí seznam přátel
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList;

use POS\Model\UserBlokedDao;

class BlokedUsersList extends UsersList {

	/** @var \POS\Model\UserBlokedDao */
	public $userBlokedDao;

	/** @var int ID uživatele - zobrazují se jeho blokovaní uživatelé */
	private $userID;

	public function __construct(UserBlokedDao $userBlokedDao, $userID, $parent, $name) {
		parent::__construct($parent, $name);

		$this->userID = $userID;
		$this->userBlokedDao = $userBlokedDao;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		parent::render();
		$this->renderTemplate(dirname(__FILE__) . '/' . 'blokedUsersList.latte');
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$blokedUsers = $this->userBlokedDao->getBlokedUsers($this->userID, $this->limit, $offset);
		$this->template->blokedUsers = $blokedUsers;
	}

	public function getSnippetName() {
		return "blokedUsersList";
	}

}
