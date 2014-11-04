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

use POS\Model\FriendDao;

class FriendsList extends UsersList {

	/** @var \POS\Model\FriendDao */
	public $friendDao;

	/** @var int ID uživatele - zobrazují se jeho přátelé */
	private $userID;

	public function __construct(FriendDao $friendDao, $userID, $parent, $name) {
		parent::__construct($parent, $name);

		$this->userID = $userID;
		$this->friendDao = $friendDao;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->renderTemplate(dirname(__FILE__) . '/' . 'friendsList.latte');
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$friends = $this->friendDao->getList($this->userID, $this->limit, $offset);
		$this->template->friends = $friends;
	}

	public function getSnippetName() {
		return "friends";
	}

}
