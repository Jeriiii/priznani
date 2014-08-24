<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Uživatelské preference pro hledání
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\UserPreferences;

use Nette\Caching\Cache;

class SearchUserPreferences extends BaseUserPreferences implements IUserPreferences {

	public function calculate() {
		$users = $this->userPropertyDao->getAll();
		$userProperty = $this->userProperty;

		/* hledání podle - hledám muže, hledám ženu ... */
		$users = $this->userPropertyDao->iWantToMeet($userProperty, $users);
		$users = $this->userPropertyDao->theyWantToMeet($userProperty, $users);

		$this->saveBestUsers($users);
	}

	public function getBestUsers() {
		$this->bestUsers = $this->cache->load(self::NAME_CACHE_USERS);
		if ($this->bestUsers === NULL) {
			$this->calculate();
		}

		return $this->bestUsers;
	}

	/**
	 *
	 * @param type $users
	 */
	public function saveBestUsers($users) {
		$arrUsers = array();
		foreach ($users as $user) {
			$arrUsers[] = $user;
		}

		$this->cache->save(self::NAME_CACHE_USERS, $arrUsers);
	}

}
