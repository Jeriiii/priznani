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
use Nette\ArrayHash;

class SearchUserPreferences extends BaseUserPreferences implements IUserPreferences {

	/**
	 * Přepočítá výsledky hledání uložené v cache. Volá se i v případě,
	 * kdy je cache prázdná.
	 */
	public function calculate() {
		$users = $this->userDao->getAll();

		$userProperty = $this->userProperty;

		/* hledání podle - hledám muže, hledám ženu ... */
		$users = $this->userDao->iWantToMeet($userProperty, $users);
		$users = $this->userDao->theyWantToMeet($userProperty, $users);

		$this->saveBestUsers($users);
	}

	public function getBestUsers() {
		$this->bestUsers = $this->section->bestUsers;

		if ($this->bestUsers === NULL) {
			$this->calculate();
		}

		return $this->bestUsers;
	}

	/**
	 * Uloží hledané uživatele do cache.
	 * @param Nette\Database\Table\Selection $users Hledaní uživatelé.
	 */
	public function saveBestUsers($users) {
		$arrUsers = array();
		foreach ($users as $user) {
			$arrUser = $user->toArray();
			$profilPhoto = $user->profilFoto;
			if (isset($profilPhoto)) {
				$profilPhoto->id;
				$arrUser["profilFoto"] = $profilPhoto->toArray();
				$gallery = $profilPhoto->gallery;
				$gallery->id;
				$arrUser["profilFoto"]["gallery"] = $gallery->toArray();
			} else {
				$arrUser["profilFoto"] = FALSE;
			}
			$arrUsers[] = ArrayHash::from($arrUser);
		}

		$this->bestUsers = $arrUsers;
		$this->section->bestUsers = $this->bestUsers;
	}

}
