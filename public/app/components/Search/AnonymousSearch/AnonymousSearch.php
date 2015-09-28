<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Slouží pro vyhledávání pro nepřihlášené uživatele, když vyplňují registraci
 * @author Jan Kotalík
 */

namespace POSComponent\Search;

use POS\Model\UserDao;
use POSComponent\BaseProjectControl;
use Nette\Http\SessionSection;
use POS\Model\UserCategoryDao;
use POS\Model\UserCategory;

class AnonymousSearch extends BaseProjectControl {

	const MAX_USERS_DISPLAYED = 9;

	private $users;
	private $doNotRender = false;

	public function __construct(SessionSection $regSession, UserDao $userDao, UserCategoryDao $userCategoryDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		if (empty($regSession->type)) {
			$this->doNotRender = true;
			return;
		}
		$this->users = $this->getUsers($regSession, $userDao, $userCategoryDao);
	}

	private function getUsers($regSession, UserDao $userDao, UserCategoryDao $userCategoryDao) {
		$mine = $userCategoryDao->getMine(UserCategory::sessionToArrayHash($regSession));
		return $userDao->getByCategories($mine, 0)->limit(self::MAX_USERS_DISPLAYED);
	}

	public function render() {
		if ($this->doNotRender) {
			return;
		}
		$this->template->setFile(dirname(__FILE__) . '/anonymousSearch.latte');
		$this->template->users = $this->users;
		$this->template->render();
	}

}
