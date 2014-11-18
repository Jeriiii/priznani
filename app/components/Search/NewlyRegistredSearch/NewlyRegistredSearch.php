<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Komponenta pro hledání nově zaregistrovaných uživatelů
 *
 * @author Petr Kukrál
 */

namespace POSComponent\Search;

use POS\Model\UserDao;

class NewlyRegistredSearch extends BaseSearch {

	public function __construct(UserDao $userDao, $parent = NULL, $name = NULL) {
		$users = $this->getNewlyRegistred($userDao);
		parent::__construct($users, $parent, $name);
	}

	private function getNewlyRegistred(UserDao $userDao) {
		$users = $userDao->getAll("DESC");
		return $users;
	}

	public function render($mode) {
		$this->renderBase($mode);
	}

}
