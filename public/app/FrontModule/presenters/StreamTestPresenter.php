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

	/**
	 * @var \POS\Model\UserPositionDao
	 * @inject
	 */
	public $userPositionDao;

	/**
	 * @var \POS\Model\EnumPositionDao
	 * @inject
	 */
	public $enumPositionDao;

	/**
	 * @var \POS\Model\UserPlaceDao
	 * @inject
	 */
	public $userPlaceDao;

	/**
	 * @var \POS\Model\EnumPlaceDao
	 * @inject
	 */
	public $enumPlaceDao;

	/**
	 * @var \POS\Model\LikeImageCommentDao
	 * @inject
	 */
	public $likeImageCommentDao;

	/**
	 * @var \POS\Model\CommentImagesDao
	 * @inject
	 */
	public $commentImagesDao;

	/**
	 * @var \POS\Model\LikeStatusCommentDao
	 * @inject
	 */
	public $likeStatusCommentDao;

	/**
	 * @var \POS\Model\CommentStatusesDao
	 * @inject
	 */
	public $commentStatusesDao;

	/**
	 * @var \POS\Model\LikeConfessionCommentDao
	 * @inject
	 */
	public $likeConfessionCommentDao;

	/**
	 * @var \POS\Model\CommentConfessionsDao
	 * @inject
	 */
	public $commentConfessionsDao;

	/**
	 * @var \POS\Model\LikeConfessionDao
	 * @inject
	 */
	public $likeConfessionDao;

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $userID;

	/** @var \Nette\Database\Table\ActiveRow Vlastnosti přihlášeného uživatele. */
	protected $userProperty;

	/** @var \Nette\Database\Table\ActiveRow Přihlášený uživatel. */
	protected $user;

	public function startup() {
		parent::startup();
		// ochrana proti spuštění na ostrém serveru nebo když je nepřihlášený
		if ($this->productionMode || !$this->getUser()->isLoggedIn()) {
			$this->redirect("OnePage:");
		}
	}

	public function actionDefault() {
		$user = $this->userDao->find($this->getUser()->getId());
		$this->user = $user;
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

		$this->template->choices = $this->streamDao->getAllItemsWhatFits($categoryIDs, $this->user->id);
		/**/
	}

	protected function createComponentUserStream() {
		return new UserStream($this->dataForStream, $this->likeStatusDao, $this->imageLikesDao, $this->userDao, $this->statusDao, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao, $this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->likeImageCommentDao, $this->commentImagesDao, $this->likeStatusCommentDao, $this->commentStatusesDao, $this->likeConfessionCommentDao, $this->commentConfessionsDao, $this->likeConfessionDao);
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

		$this->streamUserPreferences = new StreamUserPreferences($this->user, $this->userDao, $this->streamDao, $this->userCategoryDao, $this->getSession());
		$this->streamUserPreferences->calculate();
	}

}