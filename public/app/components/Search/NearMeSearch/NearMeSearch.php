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

class NearMeSearch extends BaseSearch {

	public function __construct(ActiveRow $loggedInUser, UserDao $userDao, $parent = NULL, $name = NULL) {
		$users = $this->getBestUsers($loggedInUser, $userDao);
		parent::__construct($users, $parent, $name);
	}

	private function getBestUsers(ActiveRow $loggedInUser, UserDao $userDao) {
		$users = $userDao->getNearMe($loggedInUser);
		return $users;
	}

	public function render($mode) {
		$this->renderBase($mode);
	}

}
