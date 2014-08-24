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

		dump($userProperty->id);
		dump($users->fetch());

		/* hledání podle - hledám muže, hledám ženu ... */
		$users = $this->userPropertyDao->iWantToMeet($userProperty, $users);
		//$users = $this->userPropertyDao->theyWantToMeet($userProperty, $users);

		dump($users->fetch());

		die();

		$this->saveBestUsers($users);
	}

	public function getBestUsers() {
		$this->bestUsers = $this->cache->load(self::NAME_CACHE_USERS);

		//if ($this->bestUsers === NULL) {
		$this->calculate();
		//}

		return $this->bestUsers;
	}

	/**
	 * Uloží hledané uživatele do cache.
	 * @param Nette\Database\Table\Selection $users Hledaní uživatelé.
	 */
	public function saveBestUsers($users) {
		$arrUsers = array();
		foreach ($users as $user) {
			$arrUsers[] = $user;
		}

		$this->cache->save(self::NAME_CACHE_USERS, $arrUsers);
	}

}
