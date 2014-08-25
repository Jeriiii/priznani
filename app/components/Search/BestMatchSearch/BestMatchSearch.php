<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Komponenta pro hledání s nejlepší shodou obou uživatelů podle výsledů
 * z DB.
 *
 * @author Petr Kukrál
 */

namespace POSComponent\Search;

use POS\Model\UserDao;
use Nette\Database\Table\ActiveRow;
use POS\UserPreferences\SearchUserPreferences;
use Nette\Http\Session;

class BestMatchSearch extends BaseSearch {

	public function __construct(ActiveRow $loggedInUser, UserDao $userDao, Session $session, $parent = NULL, $name = NULL) {
		$users = $this->getBestUsers($loggedInUser, $userDao, $session);
		parent::__construct($users, $parent, $name);
	}

	private function getBestUsers(ActiveRow $loggedInUser, UserDao $userDao, Session $session) {
		$searchUser = new SearchUserPreferences($loggedInUser, $userDao, $session);
		$users = $searchUser->getBestUsers();
		return $users;
	}

}
