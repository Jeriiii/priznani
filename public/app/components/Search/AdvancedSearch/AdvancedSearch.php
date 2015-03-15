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
use POS\Model\EnumBraSizeDao;
use POS\Model\EnumGraduationDao;
use POS\Model\EnumHairColourDao;
use POS\Model\EnumMaritalStateDao;
use POS\Model\EnumOrientationDao;
use POS\Model\EnumPenisWidthDao;
use POS\Model\EnumPropertyDao;
use POS\Model\EnumShapeDao;
use POS\Model\EnumSmokeDao;
use POS\Model\EnumTallnessDao;

class AdvancedSearch extends BaseSearch {

	/**
	 * @var \POS\Model\CityDao
	 */
	public $cityDao;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\EnumBraSizeDao
	 */
	public $enumBraSizeDao;

	/**
	 * @var \POS\Model\EnumGraduationDao
	 */
	public $enumGraduationDao;

	/**
	 * @var \POS\Model\EnumHairColourDao
	 */
	public $enumHairColourDao;

	/**
	 * @var \POS\Model\EnumMaritalStateDao
	 */
	public $enumMaritalStateDao;

	/**
	 * @var \POS\Model\EnumOrientationDao
	 */
	public $enumOrientationDao;

	/**
	 * @var \POS\Model\EnumPenisWidthDao
	 */
	public $enumPenisWidthDao;

	/**
	 * @var \POS\Model\EnumPropertyDao
	 */
	public $enumPropertyDao;

	/**
	 * @var \POS\Model\EnumShapeDao
	 */
	public $enumShapeDao;

	/**
	 * @var \POS\Model\EnumSmokeDao
	 */
	public $enumSmokeDao;

	/**
	 * @var \POS\Model\EnumTallnessDao
	 */
	public $enumTallnessDao;

	public function __construct(EnumBraSizeDao $enumBraSizeDao, EnumGraduationDao $enumGraduationDao, EnumHairColourDao $enumHairColourDao, EnumMaritalStateDao $enumMaritalStateDao, EnumOrientationDao $enumOrientationDao, EnumPenisWidthDao $enumPenisWidthDao, EnumPropertyDao $enumPropertyDao, EnumShapeDao $enumShapeDao, EnumSmokeDao $enumSmokeDao, EnumTallnessDao $enumTallnessDao, CityDao $cityDao, UserDao $userDao, $searchData, $parent = NULL, $name = NULL) {
		$this->userDao = $userDao;
		$this->enumBraSizeDao = $enumBraSizeDao;
		$this->enumGraduationDao = $enumGraduationDao;
		$this->enumHairColourDao = $enumHairColourDao;
		$this->enumMaritalStateDao = $enumMaritalStateDao;
		$this->enumOrientationDao = $enumOrientationDao;
		$this->enumPenisWidthDao = $enumPenisWidthDao;
		$this->enumPropertyDao = $enumPropertyDao;
		$this->enumShapeDao = $enumShapeDao;
		$this->enumSmokeDao = $enumSmokeDao;
		$this->enumTallnessDao = $enumTallnessDao;

		$users = $this->getUsers($searchData);
		parent::__construct($users, $parent, $name);
		$this->cityDao = $cityDao;
	}

	/**
	 * Komponenta s formulářem
	 * @param type $name
	 * @return \Nette\Application\UI\Form\AdvancedForm
	 */
	public function createComponentAdvancedSearchForm($name) {
		return new Frm\AdvancedForm($this->enumBraSizeDao, $this->enumGraduationDao, $this->enumHairColourDao, $this->enumMaritalStateDao, $this->enumOrientationDao, $this->enumPenisWidthDao, $this->enumPropertyDao, $this->enumShapeDao, $this->enumSmokeDao, $this->enumTallnessDao, $this->cityDao, $this, $name);
	}

	public function render($mode) {
		$this->template->setFile(dirname(__FILE__) . '/advancedSearch.latte');

		$this->template->render();
		$this->renderBase($mode);
	}

	/**
	 * Vyhledá v db uživatele podle zadaných dat
	 * @param array $searchData
	 * @return Nette\Database\Table\Selection
	 */
	private function getUsers($searchData) {
		$users = $this->userDao->getBySearchData($searchData);
		return $users;
	}

}
