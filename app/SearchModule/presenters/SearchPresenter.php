<?php

namespace SearchModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer,
	Nette\DateTime;
use Nette\Diagnostics\Debugger;
use POS\UserPreferences\SearchUserPreferences;
use POSComponent\Search\BestMatchSearch;

class SearchPresenter extends SearchBasePresenter {

	private $user;
	private $fotos;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\CityDao
	 * @inject
	 */
	public $cityDao;

	/**
	 * uchovává parametry pro podrobné hledání
	 * @var array
	 */
	public $searchData;

	/**
	 * @var \POS\Model\UserCategoryDao
	 * @inject
	 */
	public $userCategoryDao;

	/**
	 * @var \POS\Model\EnumBraSizeDao
	 * @inject
	 */
	public $enumBraSizeDao;

	/**
	 * @var \POS\Model\EnumGraduationDao
	 * @inject
	 */
	public $enumGraduationDao;

	/**
	 * @var \POS\Model\EnumHairColourDao
	 * @inject
	 */
	public $enumHairColourDao;

	/**
	 * @var \POS\Model\EnumMaritalStateDao
	 * @inject
	 */
	public $enumMaritalStateDao;

	/**
	 * @var \POS\Model\EnumOrientationDao
	 * @inject
	 */
	public $enumOrientationDao;

	/**
	 * @var \POS\Model\EnumPenisWidthDao
	 * @inject
	 */
	public $enumPenisWidthDao;

	/**
	 * @var \POS\Model\EnumPropertyDao
	 * @inject
	 */
	public $enumPropertyDao;

	/**
	 * @var \POS\Model\EnumShapeDao
	 * @inject
	 */
	public $enumShapeDao;

	/**
	 * @var \POS\Model\EnumSmokeDao
	 * @inject
	 */
	public $enumSmokeDao;

	/**
	 * @var \POS\Model\EnumTallnessDao
	 * @inject
	 */
	public $enumTallnessDao;

	public function beforeRender() {
		parent::beforeRender();
		$this->setSexMode();
	}

	/*
	 * vrati celeho uzivatele z databaze
	 */

	public function getUserFromDB() {
		return $this->context->createUsers()
				->getUser($this->getUser()->id)
				->fetch();
	}

	/*
	 * vrati novy strankovac
	 */

	public function getPaginator($items, $itemsPerPage) {
		$vp = new \VisualPaginator($this, 'vp');
		$page = $vp->page;
		$paginator = $vp->getPaginator();
		$paginator->setItemCount($items->count()); // celkový počet položek
		$paginator->setItemsPerPage($itemsPerPage); // počet položek na stránce
		$paginator->setPage($page); // číslo aktuální stránky

		return $items->limit($paginator->getLength(), $paginator->getOffset());
	}

	public function actionAdvancedSearch($interested_in_men, $interested_in_women, $interested_in_couple, $interested_in_couple_men, $interested_in_couple_women, $interested_in_group, $orientation = null, $age_from = null, $age_to = null, $tallness = null, $shape = null, $smoke = null, $drink = null, $graduation = null) {
		$want_to_meet_men = '';
		$want_to_meet_women = '';
		$want_to_meet_couple = '';
		$want_to_meet_couple_men = '';
		$want_to_meet_couple_women = '';
		$want_to_meet_group = '';

		/* ORIENTATION */
		if ($orientation != null) {
			$whereOrientation = '';
			if ($orientation == 'hetero') {
				$whereOrientation .= '"hetero",';
			}
			if ($orientation == 'homo') {
				$whereOrientation .= '"homo",';
			}
			if ($orientation == 'bi') {
				$whereOrientation .= '"bi",';
			}
			if ($orientation == 'biTry') {
				$whereOrientation .= '"biTry",';
			}
			$filterOrientation = rtrim($whereOrientation, ',');
		} else {
			$filterOrientation = NULL;
		}

		/* CHECKBOXES */
		if ($interested_in_men != null || $interested_in_women != null || $interested_in_couple != null || $interested_in_couple_men != null || $interested_in_couple_women != null || $interested_in_group != null) {
			$whereInterested = '';
			if ($interested_in_men == 1) {
				$whereInterested .= '"man",';
				$want_to_meet_men = 'want_to_meet_men';
			}
			if ($interested_in_women == 1) {
				$whereInterested .= '"woman",';
				$want_to_meet_women = 'want_to_meet_women';
			}
			if ($interested_in_couple == 1) {
				$whereInterested .= '"couple",';
				$want_to_meet_couple = 'want_to_meet_couple';
			}
			if ($interested_in_couple_men == 1) {
				$whereInterested .= '"coupleMan",';
				$want_to_meet_couple_men = 'want_to_meet_couple_men';
			}
			if ($interested_in_couple_women == 1) {
				$whereInterested .= '"coupleWoman",';
				$want_to_meet_couple_women = 'want_to_meet_couple_women';
			}
			if ($interested_in_group == 1) {
				$whereInterested .= '"group",';
				$want_to_meet_group = 'want_to_meet_group';
			}
			$filterInterested = rtrim($whereInterested, ',');
		} else {
			$filterInterested = NULL;
		}

		/* AGE */
		if ($age_from != null && $age_to != null) {
			$filterAge = array('from' => $age_from, 'to' => $age_to);
		} else {
			$filterAge = NULL;
		}

		/* TALLNESS */
		if ($tallness != null) {
			$whereTallness = '';
			if ($tallness == '160') {
				$whereTallness .= '"160",';
			}
			if ($tallness == '170') {
				$whereTallness .= '"170",';
			}
			if ($tallness == '180') {
				$whereTallness .= '"180",';
			}
			if ($tallness == '190') {
				$whereTallness .= '"190",';
			}
			if ($tallness == '200') {
				$whereTallness .= '"200",';
			}
			$filterTallness = rtrim($whereTallness, ',');
		} else {
			$filterTallness = NULL;
		}

		/* SHAPE */
		if ($shape != null) {
			$whereShape = '';
			if ($shape == '0') {
				$whereShape .= '"0",';
			}
			if ($shape == '1') {
				$whereShape .= '"1",';
			}
			if ($shape == '2') {
				$whereShape .= '"2",';
			}
			if ($shape == '3') {
				$whereShape .= '"3",';
			}
			if ($shape == '4') {
				$whereShape .= '"4",';
			}
			if ($shape == '5') {
				$whereShape .= '"5",';
			}
			$filterShape = rtrim($whereShape, ',');
		} else {
			$filterShape = NULL;
		}

		/* Smoke */
		if ($smoke != null) {
			$whereSmoke = '';
			if ($smoke == 'often') {
				$whereSmoke .= '"often",';
			}
			if ($smoke == 'no') {
				$whereSmoke .= '"no",';
			}
			if ($smoke == 'occasionlly') {
				$whereSmoke .= '"occasionlly",';
			}
			$filterSmoke = rtrim($whereSmoke, ',');
		} else {
			$filterSmoke = NULL;
		}

		/* Drink */
		if ($drink != null) {
			$whereDrink = '';
			if ($drink == 'often') {
				$whereDrink .= '"often",';
			}
			if ($drink == 'no') {
				$whereDrink .= '"no",';
			}
			if ($drink == 'occasionlly') {
				$whereDrink .= '"occasionlly",';
			}
			$filterDrink = rtrim($whereDrink, ',');
		} else {
			$filterDrink = NULL;
		}

		/* Graduation */
		if ($graduation != null) {
			$whereGraduation = '';
			if ($graduation == 'zs') {
				$whereGraduation .= '"zs",';
			}
			if ($graduation == 'sou') {
				$whereGraduation .= '"sou",';
			}
			if ($graduation == 'sos') {
				$whereGraduation .= '"sos",';
			}
			if ($graduation == 'vos') {
				$whereGraduation .= '"vos",';
			}
			if ($graduation == 'vs') {
				$whereGraduation .= '"vs",';
			}
			$filterGraduation = rtrim($whereGraduation, ',');
		} else {
			$filterGraduation = NULL;
		}

//pole obsahující jednotlivé části dotazu uživatele (některé pole mohou být prázdné)
		$AdvancedFilter = array(
			'orientation' => $filterOrientation,
			'type' => $filterInterested,
			'age' => $filterAge,
			'tallness' => $filterTallness,
			'shape' => $filterShape,
			'smoke' => $filterSmoke,
			'drink' => $filterDrink,
			'graduation' => $filterGraduation
		);

//cyklus, který filtruje dotaz podle neprázdných, vyplněných, uživatelských údajů
		$where = array();
		foreach ($AdvancedFilter as $varname => $varvalue) {
			if ($varname == 'age' && !empty($varvalue)) {
				$where[] = " $varname BETWEEN " . $varvalue['from'] . " AND " . $varvalue['to'] . "";
			} elseif (trim($varvalue) != '') {
				$where[] = " $varname IN (" . $varvalue . ")";
			}
		}

		$advancedUsersData = $this->context->createUsers()->where($where);
		$this->template->advancedUsersData = $this->getPaginator($advancedUsersData, "12");
		$this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');

// převyplněný předchozí výběr uživatele
		$this['advancedSearchForm']->setDefaults(array(
			'orientation' => $orientation,
			'interested_in' => array(
				$want_to_meet_men,
				$want_to_meet_women,
				$want_to_meet_couple,
				$want_to_meet_couple_men,
				$want_to_meet_couple_women,
				$want_to_meet_group
			),
			'age_from' => $age_from,
			'age_to' => $age_to,
			'tallness' => $tallness,
			'shape' => $shape,
			'smoke' => $smoke,
			'drink' => $drink,
			'graduation' => $graduation,
		));
	}

	public function renderLastHour($time = "1 HOUR") {

		$usersData = $this->context->createUsers()->getUsersLastActive($this->getUserFromDB(), $time);

		if ($usersData->count() < 2) {
			$this->template->usersDataLastDay = true;
		} else {
			$this->template->usersDataLastDay = false;
		}
//	Debugger::dump($this->template->usersDataLastDay);
		$this->template->usersData = $this->getPaginator($usersData, "4");
		$this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');
	}

	public function renderLast24h($time = "1 DAY") {

		$usersLastDay = $this->context->createUsers()->getUsersLastActive($this->getUserFromDB(), $time);

		$this->template->usersLastDay = $this->getPaginator($usersLastDay, "4");
		$this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');
	}

	public function renderNewlyRegistered() {

		$usersData = $this->context->createUsers()->getUsersNewlyRegistered($this->getUserFromDB());


		$this->template->usersData = $this->getPaginator($usersData, "4");
		$this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');
	}

	public function renderDefault() {

	}

	/*
	 * v případě parametrů z hledání se tyto uloží do pole
	 */

	public function actionAdvanced($age_from, $age_to, $sex, $penis_length_from, $penis_length_to, $penis_width, $bra_size, $orientation, $shape, $hair_color, $tallness_from, $tallness_to, $drink, $smoke, $marital_state, $graduation, $city, $district, $region, $men, $women, $couple, $men_couple, $women_couple, $more, $threesome, $anal, $group, $bdsm, $swallow, $cum, $oral, $piss, $sex_massage, $petting, $fisting, $deepthroat) {

		$data = array();

		if ($age_from != NULL) {
			$data['age_from'] = $age_from;
		}
		if ($age_to != NULL) {
			$data['age_to'] = $age_to;
		}
		if ($sex != NULL) {
			$data['sex'] = $sex;
		}
		if ($penis_length_from != NULL) {
			$data['penis_length_from'] = $penis_length_from;
		}
		if ($penis_length_to != NULL) {
			$data['penis_length_to'] = $penis_length_to;
		}
		if ($penis_width != NULL) {
			$data['penis_width'] = $penis_width;
		}
		if ($bra_size != NULL) {
			$data['bra_size'] = $bra_size;
		}
		if ($orientation != NULL) {
			$data['orientation'] = $orientation;
		}
		if ($shape != NULL) {
			$data['shape'] = $shape;
		}
		if ($hair_color != NULL) {
			$data['hair_color'] = $hair_color;
		}
		if ($tallness_from != NULL) {
			$data['tallness_from'] = $tallness_from;
		}
		if ($tallness_to != NULL) {
			$data['tallness_to'] = $tallness_to;
		}
		if ($drink != NULL) {
			$data['drink'] = $drink;
		}
		if ($smoke != NULL) {
			$data['smoke'] = $smoke;
		}
		if ($city != NULL) {
			$data['city'] = $city;
		}
		if ($district != NULL) {
			$data['district'] = $district;
		}
		if ($region != NULL) {
			$data['region'] = $region;
		}
		if ($men != NULL) {
			$data['men'] = $men;
		}
		if ($men != NULL) {
			$data['women'] = $women;
		}
		if ($men != NULL) {
			$data['couple'] = $couple;
		}
		if ($men_couple != NULL) {
			$data['men_couple'] = $men_couple;
		}
		if ($women_couple != NULL) {
			$data['women_couple'] = $women_couple;
		}
		if ($more != NULL) {
			$data['more'] = $more;
		}
		if ($marital_state != NULL) {
			$data['marital_state'] = $marital_state;
		}
		if ($graduation != NULL) {
			$data['graduation'] = $graduation;
		}
		if ($threesome != NULL) {
			$data['threesome'] = $threesome;
		}
		if ($anal != NULL) {
			$data['anal'] = $anal;
		}
		if ($group != NULL) {
			$data['group'] = $group;
		}
		if ($bdsm != NULL) {
			$data['bdsm'] = $bdsm;
		}
		if ($swallow != NULL) {
			$data['swallow'] = $swallow;
		}
		if ($cum != NULL) {
			$data['cum'] = $cum;
		}
		if ($oral != NULL) {
			$data['oral'] = $oral;
		}
		if ($piss != NULL) {
			$data['piss'] = $piss;
		}
		if ($sex_massage != NULL) {
			$data['sex_massage'] = $sex_massage;
		}
		if ($petting != NULL) {
			$data['petting'] = $petting;
		}
		if ($fisting != NULL) {
			$data['fisting'] = $fisting;
		}
		if ($deepthroat != NULL) {
			$data['deepthroat'] = $deepthroat;
		}


		$this->searchData = $data;
	}

	protected function createComponentAdvancedSearchForm($name) {
		$form = new Frm\AdvancedSearchForm($this, $name);
		return $form;
	}

	protected function createComponentBaseSearch($name) {
		$users = $this->userDao->getAll();
		$form = new \POSComponent\Search\BaseSearch($users);
		return $form;
	}

	protected function createComponentBestMatchSearch($name) {
		$user = $this->userDao->find($this->getUser()->id);
		$session = $this->getSession();
		return new BestMatchSearch($user, $this->userDao, $this->userCategoryDao, $session, $this, $name);
	}

	/**
	 * Komponenta s formulářem pro podrobné vyhledávání
	 * @param type $name
	 * @return \POSComponent\Search\AdvancedSearch
	 */
	protected function createComponentAdvancedSearch($name) {
		return new \POSComponent\Search\AdvancedSearch($this->enumBraSizeDao, $this->enumGraduationDao, $this->enumHairColourDao, $this->enumMaritalStateDao, $this->enumOrientationDao, $this->enumPenisWidthDao, $this->enumPropertyDao, $this->enumShapeDao, $this->enumSmokeDao, $this->enumTallnessDao, $this->cityDao, $this->userDao, $this->searchData, $this, $name);
	}

}
