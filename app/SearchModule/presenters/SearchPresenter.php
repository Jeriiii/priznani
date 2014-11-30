<?php

namespace SearchModule;

use Nette\Application\UI\Form as Frm;
use POSComponent\Search\BestMatchSearch;
use POSComponent\Search\NewlyRegistredSearch;
use POSComponent\Search\NearMeSearch;
use POSComponent\Search\AdvancedSearch;
use POSComponent\Search\VigorSearch;

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
	 * @var \POS\Model\DistrictDao
	 * @inject
	 */
	public $districtDao;

	/**
	 * @var \POS\Model\RegionDao
	 * @inject
	 */
	public $regionDao;

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

	/**
	 * @var \POS\Model\EnumVigorDao
	 * @inject
	 */
	public $enumVigorDao;

	/** @var int|boolean Znamení co se vyhledává. Když není zadáno = FALSE. */
	private $vigor;

	public function beforeRender() {
		parent::beforeRender();
		$this->setSexMode();
	}

	public function renderNearMe() {
		$this->template->cityData = $this->cityDao->getNamesOfProperties();
		$this->template->property = $this->loggedUser->property;
		$this->template->city = $this->cityDao->find($this->loggedUser->property->cityID)->name;
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

	/*
	 * v případě parametrů z hledání se tyto uloží do pole
	 */

	public function actionAdvanced(array $data) {
		/* posílá se desítky parametrů, proto je načítám přes httpRequest */
		$httpRequest = $this->context->httpRequest;
		$this->searchData = $httpRequest->getQuery();
	}

	public function actionVigor($vigor = NULL) {
		$this->vigor = $vigor;
	}

	protected function createComponentAdvancedSearchForm($name) {
		$form = new Frm\AdvancedSearchForm($this, $name);
		return $form;
	}

	protected function createComponentNearMeSearch($name) {
		$user = $this->userDao->find($this->getUser()->id);
		return new NearMeSearch($user, $this->userDao, $this, $name);
	}

	protected function createComponentNewlyRegistredSearch($name) {
		return new NewlyRegistredSearch($this->userDao, $this, $name);
	}

	protected function createComponentVigorSearch($name) {
		return new VigorSearch($this->userDao, $this->vigor, $this, $name);
	}

	protected function createComponentVigorSearchForm($name) {
		return new Frm \ SelectVigorForm($this->enumVigorDao, $this->vigor, $this, $name);
	}

	protected function createComponentEditCityForm($name) {
		$property = $this->userDao->findProperties($this->getUser()->id);
		return new Frm \ EditCityForm($this->regionDao, $this->districtDao, $this->cityDao, $this->userPropertyDao, $property, $this, $name);
	}

	protected function createComponentBestMatchSearch($name) {
		$user = $this->userDao->find($this->getUser()->id);
		$session = $this->getSession();
		return new BestMatchSearch($user, $this->userDao, $this->userCategoryDao, $session, $this, $name);
	}

	/**
	 * WebLoader pro minifikace skriptu
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new \JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	/**
	 * Komponenta s formulářem pro podrobné vyhledávání
	 * @param type $name
	 * @return \POSComponent\Search\AdvancedSearch
	 */
	protected function createComponentAdvancedSearch($name) {
		return new AdvancedSearch($this->enumBraSizeDao, $this->enumGraduationDao, $this->enumHairColourDao, $this->enumMaritalStateDao, $this->enumOrientationDao, $this->enumPenisWidthDao, $this->enumPropertyDao, $this->enumShapeDao, $this->enumSmokeDao, $this->enumTallnessDao, $this->cityDao, $this->userDao, $this->searchData, $this, $name);
	}

}
