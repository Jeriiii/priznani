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
use POS\Model\UserCategoryDao;

class BestMatchSearch extends BaseSearch {

	public function __construct($loggedInUser, UserDao $userDao, UserCategoryDao $userCategoryDao, Session $session, $parent = NULL, $name = NULL) {
		if (!($loggedInUser instanceof ActiveRow) && !($loggedInUser instanceof \Nette\ArrayHash)) {
			throw new Exception("variable user must be instance of ActiveRow or ArrayHash");
		}

		$users = $this->getBestUsers($loggedInUser, $userDao, $userCategoryDao, $session);
		parent::__construct($users, $parent, $name);
	}

	private function getBestUsers($loggedInUser, UserDao $userDao, UserCategoryDao $userCategoryDao, Session $session) {
		$searchUser = new SearchUserPreferences($loggedInUser, $userDao, $userCategoryDao, $session);
		$users = $searchUser->getBestUsers();
		return $users;
	}

	public function render($mode) {
		$this->renderBase($mode);
	}

}
