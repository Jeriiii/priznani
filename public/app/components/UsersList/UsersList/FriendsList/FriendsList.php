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

	/* info o tom, zda se obsah donačítá (když true, obsah je zobrazen vždy všechen) */
	private $listAll = FALSE;

	public function __construct(FriendDao $friendDao, $userID, $parent, $name, $listAll = FALSE) {
		parent::__construct($parent, $name);

		$this->userID = $userID;
		$this->friendDao = $friendDao;
		$this->listAll = $listAll;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		parent::render();
		if ($this->listAll) {
			$this->template->friends = $this->friendDao->getList($this->userID);
		}
		if ($this->getEnvironment()->isMobile()) {
			$this->renderTemplate(dirname(__FILE__) . '/' . 'mobileFriendsList.latte');
		} else {
			$this->renderTemplate(dirname(__FILE__) . '/' . 'friendsList.latte');
		}
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
