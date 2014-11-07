<?php

namespace SearchModule;

use Nette\Application\UI\Form as Frm;
use POSComponent\Search\BestMatchSearch;
use POSComponent\Search\NewlyRegistredSearch;
use POSComponent\Search\NearMeSearch;
use POSComponent\Search\AdvancedSearch;

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
		return new AdvancedSearch($this->enumBraSizeDao, $this->enumGraduationDao, $this->enumHairColourDao, $this->enumMaritalStateDao, $this->enumOrientationDao, $this->enumPenisWidthDao, $this->enumPropertyDao, $this->enumShapeDao, $this->enumSmokeDao, $this->enumTallnessDao, $this->cityDao, $this->userDao, $this->searchData, $this, $name);
	}

}
