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
use NetteExt\Serialize\Relation;
use NetteExt\Serialize\Serializer;
use POS\Model\UserCategory;

class SearchUserPreferences extends BaseUserPreferences implements IUserPreferences {

	/**
	 * Přepočítá výsledky hledání uložené v cache. Volá se i v případě,
	 * kdy je cache prázdná.
	 */
	public function calculate() {
		$categoryIDs = $this->getUserCategories(TRUE);
		$users = $this->userDao->getByCategories($categoryIDs, $this->user->id);

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
		$relProfilPhoto = new Relation("profilFoto");
		$relGallery = new Relation("gallery");
		$relProperty = new Relation("property");
		$relProfilPhoto->addRel($relGallery);

		$ser = new Serializer($users);
		$ser->addRel($relProfilPhoto);
		$ser->addRel($relProperty);

		$this->bestUsers = $ser->toArrayHash();
		$this->section->bestUsers = $this->bestUsers;
	}

}