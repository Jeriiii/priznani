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

class VigorSearch extends BaseSearch {

	public function __construct(UserDao $userDao, $vigor, $parent = NULL, $name = NULL) {
		$users = $this->getBestUsers($userDao, $vigor);
		parent::__construct($users, $parent, $name);
	}

	private function getBestUsers(UserDao $userDao, $vigor) {
		if ($vigor == FALSE) { // nebylo ještě vybráno znamení
			return array();
		}
		$users = $userDao->getByVigor($vigor);
		return $users;
	}

	public function render($mode) {
		$this->renderBase($mode);
	}

}
