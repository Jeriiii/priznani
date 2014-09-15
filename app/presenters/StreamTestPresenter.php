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
	 * @var \POS\Model\StreamCategoriesDao
	 * @inject
	 */
	public $streamCategoriesDao;

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

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $userID;

	public function startup() {
		parent::startup();
		// ochrana proti spuštění na ostrém serveru nebo když je nepřihlášený
		if ($this->productionMode || !$this->getUser()->isLoggedIn()) {
			$this->redirect("OnePage:");
		}
	}

	public function actionDefault() {
		$this->initializeStreamUserPreferences();
		$this->fillCorrectDataForStream();
		$this->userID = $this->getUser()->getId();
	}

	public function renderDefault() {
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);

		/* příklady použití nových dao funkcí */
		$this->template->categories = $this->streamCategoriesDao->getCategoriesWhatFit(array(
			StreamCategoriesDao::COLUMN_ANAL => 1,
			StreamCategoriesDao::COLUMN_SWALLOW => 1,
			StreamCategoriesDao::COLUMN_GROUP => 0,
			StreamCategoriesDao::COLUMN_ORAL => 1
		));
		$this->template->choices = $this->streamDao->getAllItemsWhatFits(array(1, 2, 3));
		$this->template->results = $this->streamDao->getAllItemsWhatFitsAndRange(array(1, 2, 3), array(
			'age' => array('2014-06-26 00:27:02', '2014-09-26 22:27:02'),
			'tallness' => array(180, 200)
		));
	}

	protected function createComponentUserStream() {
		return new UserStream($this->dataForStream, $this->userDao, $this->statusDao, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao);
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
		$userProperty = $this->userPropertyDao->find($this->getUser()->getId());
		$this->streamUserPreferences = new StreamUserPreferences($userProperty, $this->userDao, $this->streamDao, $this->streamCategoriesDao, $this->getSession());
	}

}
