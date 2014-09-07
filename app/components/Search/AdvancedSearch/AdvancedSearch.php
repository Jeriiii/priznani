<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Komponenta pro podrobné hledání uživatelů
 *
 * @author Daniel Holubář
 */

namespace POSComponent\Search;

use POS\Model\UserDao;
use Nette\Database\Table\ActiveRow;
use POS\UserPreferences\SearchUserPreferences;
use Nette\Application\UI\Form as Frm;
use Nette\Http\Session;
use POS\Model\CityDao;

class AdvancedSearch extends BaseSearch {

	/**
	 * @var \POS\Model\CityDao
	 */
	public $cityDao;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	public function __construct(CityDao $cityDao, UserDao $userDao, $searchData, $parent = NULL, $name = NULL) {
		$this->userDao = $userDao;

		//pokud nejsou vyhledávací data, nevykreslí žádné uživatele
		if (empty($searchData['age_from'])) {
			parent::__construct(array(), $parent, $name);
		} else {
			$users = $this->getusers($searchData);
			parent::__construct($users, $parent, $name);
		}
		$this->cityDao = $cityDao;
	}

	/**
	 * Komponenta s formulářem
	 * @param type $name
	 * @return \Nette\Application\UI\Form\AdvancedForm
	 */
	public function createComponentAdvancedSearchForm($name) {
		return new Frm\AdvancedForm($this->cityDao, $this, $name);
	}

	public function render($mode) {
		$this->renderBase($mode);
		$this->template->setFile(dirname(__FILE__) . '/advancedSearch.latte');

		$this->template->render();
	}

	/**
	 * Vyhledá v db uživatele podle zadaných dat
	 * @param array $searchData
	 * @return Nette\Database\Table\Selection
	 */
	private function getusers($searchData) {
		$users = $this->userDao->findBySearchData($searchData);
		return $users;
	}

}
