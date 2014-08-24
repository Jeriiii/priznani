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

use POS\Model\UserPropertyDao;
use Nette\Database\Table\ActiveRow;
use POS\UserPreferences\SearchUserPreferences;

class BestMatchSearch extends BaseSearch {

	public function __construct(ActiveRow $loggedInUser, UserPropertyDao $userPropertyDao, $parent = NULL, $name = NULL) {
		$users = $this->getBestUsers($loggedInUser, $userPropertyDao);
		parent::__construct($users, $parent, $name);
	}

	private function getBestUsers(ActiveRow $loggedInUser, UserPropertyDao $userPropertyDao) {
		$searchUser = new SearchUserPreferences($loggedInUser, $userPropertyDao);
		$users = $searchUser->getBestUsers();
		return $users;
	}

}
