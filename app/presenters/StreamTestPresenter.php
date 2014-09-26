<?php

/**
 *
 * Testovací presenter pro stream. Nepřístupný na produkci.
 *
 * @author     Jan Kotalík
 */
use POSComponent\Stream\UserStream\UserStream;
use POS\Model\StreamCategoriesDao;
use POS\UserPreferences\StreamUserPreferences;

class StreamTestPresenter extends BasePresenter {

	/**
	 *
	 * @var \POS\UserPreferences\StreamUserPreferences
	 */
	public $streamUserPreferences;

	/**
	 * @var \POS\Model\UserCategoryDao
	 * @inject
	 */
	public $userCategoryDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

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
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\StatusDao
	 * @inject
	 */
	public $statusDao;

	/**
	 * @var \POS\Model\ImageLikesDao
	 * @inject
	 */
	public $imageLikesDao;

	/**
	 * @var \POS\Model\LikeStatusDao
	 * @inject
	 */
	public $likeStatusDao;

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $userID;

	/** @var \Nette\Database\Table\ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	public function startup() {
		parent::startup();
		// ochrana proti spuštění na ostrém serveru nebo když je nepřihlášený
		if ($this->productionMode || !$this->getUser()->isLoggedIn()) {
			$this->redirect("OnePage:");
		}
	}

	public function actionDefault() {
		$user = $this->userDao->find($this->getUser()->getId());
		$this->userProperty = $user->property;
		$this->initializeStreamUserPreferences();
		$this->fillCorrectDataForStream();
		$this->userID = $this->getUser()->getId();
	}

	public function renderDefault() {
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);

		/* příklady použití nových dao funkcí */
		$userCategory = new POS\Model\UserCategory($this->userProperty, $this->userCategoryDao, $this->getSession());
		$categoryIDs = $userCategory->getCategoryIDs(TRUE);
		$this->template->categories = $categoryIDs;
		$this->template->choices = $this->streamDao->getAllItemsWhatFits($categoryIDs);
		/**/
	}

	protected function createComponentUserStream() {
		return new UserStream($this->dataForStream, $this->likeStatusDao, $this->imageLikesDao, $this->statusDao, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao);
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'nette.ajax.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentSearch() {
		$component = new Search();
		return $component;
	}

	private function fillCorrectDataForStream() {
		$this->dataForStream = $this->streamUserPreferences->getBestStreamItems();
	}

	private function initializeStreamUserPreferences() {
		$this->streamUserPreferences = new StreamUserPreferences($this->userProperty, $this->userDao, $this->streamDao, $this->userCategoryDao, $this->getSession());
		$this->streamUserPreferences->calculate();
	}

}
