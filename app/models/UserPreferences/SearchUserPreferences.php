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
use POS\Model\UserDao;
use Nette\Http\Session;
use POS\Model\UserCategoryDao;

class SearchUserPreferences extends BaseUserPreferences implements IUserPreferences {

	public function __construct($user, UserDao $userDao, UserCategoryDao $userCategoryDao, Session $session) {
		parent::__construct($user, $userDao, $userCategoryDao, $session, $expirationTime = '3 min');
	}

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
//		$this->bestUsers = $this->section->bestUsers;
		$bestUserIds = $this->section->bestUsers;

		if ($bestUserIds === NULL) {
			$this->calculate();
		}

		$bestUsers = $this->userDao->getInIds($bestUserIds);

		return $bestUsers;
	}

	/**
	 * Uloží hledané uživatele do cache.
	 * @param Nette\Database\Table\Selection $users Hledaní uživatelé.
	 */
	public function saveBestUsers($users) {
//		$relProfilPhoto = new Relation("profilFoto");
//		$relGallery = new Relation("gallery");
//		$relProperty = new Relation("property");
//		$relProfilPhoto->addRel($relGallery);

		$userIds = array();

		foreach ($users as $user) {
			$userIds[] = $user->id;
		}

//		$ser = new Serializer($userIds);
//		$ser->addRel($relProfilPhoto);
//		$ser->addRel($relProperty);



		$this->bestUsers = $userIds; //$ser->toArrayHash();
		$this->section->bestUsers = $this->bestUsers;
	}

}
