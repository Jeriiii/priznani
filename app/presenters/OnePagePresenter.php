<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\UserStream\UserStream;
use POS\UserPreferences\StreamUserPreferences;
use POSComponent\UsersList\FriendRequestList;
use POSComponent\UsersList\FriendsList;
use POSComponent\UsersList\BlokedUsersList;
use POSComponent\UsersList\SexyList\MarkedFromOther;

class OnePagePresenter extends BasePresenter {

	/**
	 * Vybere do streamu nejvhodnější data vzhledem k preferencím daného uživatele
	 * @var \POS\UserPreferences\StreamUserPreferences
	 */
	public $streamUserPreferences;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\UserBlokedDao
	 * @inject
	 */
	public $userBlokedDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

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
	 * @var \POS\Model\FriendRequestDao
	 * @inject
	 */
	public $friendRequestDao;

	/**
	 * @var \POS\Model\FriendDao
	 * @inject
	 */
	public $friendDao;

	/**
	 * @var \POS\Model\UserCategoryDao
	 * @inject
	 */
	public $userCategoryDao;

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
	 * @var \POS\Model\LikeCommentDao
	 * @inject
	 */
	public $likeCommentDao;

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
	public $likeCOnfessionCommentDao;

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

	/**
	 * @var \POS\Model\VerificationPhotoRequestsDao
	 * @inject
	 */
	public $verificationPhotoDao;

	/**
	 * @var \POS\Model\YouAreSexyDao
	 * @inject
	 */
	public $youAreSexyDao;

	/**
	 * @var \POS\Model\PaymentDao
	 * @inject
	 */
	public $paymentDao;

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $userID;
	protected $userData;

	public function actionDefault() {
		$this->userID = $this->getUser()->getId();
		$this->userData = $this->userDao->find($this->userID);
		$this->fillCorrectDataForStream();
	}

	public function renderDefault() {
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);
		$this->template->userData = $this->userData;
		$this->template->countFriendRequests = $this->friendRequestDao->getAllToUser($this->userID)->count();
		$this->template->countSexy = $this->youAreSexyDao->countToUser($this->userID);
		$this->template->isUserPaying = $this->paymentDao->isUserPaying($this->userID);
		$this->template->countVerificationRequests = $this->verificationPhotoDao->findByUserID($this->user->id)->count();
	}

	protected function createComponentUserStream() {
		return new UserStream($this->dataForStream, $this->likeStatusDao, $this->imageLikesDao, $this->userDao, $this->statusDao, $this->streamDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao, $this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->likeCommentDao, $this->commentImagesDao, $this->likeStatusCommentDao, $this->commentStatusesDao, $this->likeCOnfessionCommentDao, $this->commentConfessionsDao, $this->likeConfessionDao);
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'lists/initFriendRequest.js',
			'lists/initFriends.js',
			'lists/initBlokedUsers.js',
			'lists/initMarkedFromOther.js'
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

	/**
	 * vrací nejlepší osoby pro seznámení s uživatelem
	 * @param type $name
	 * @return \POSComponent\Search\BestMatchSearch
	 */
	protected function createComponentBestMatchSearch($name) {
		$session = $this->getSession();
		return new \POSComponent\Search\BestMatchSearch($this->userData, $this->userDao, $this->userCategoryDao, $session, $this, $name);
	}

	protected function createComponentFriendRequest($name) {
		return new FriendRequestList($this->friendRequestDao, $this->getUser()->id, $this, $name);
	}

	protected function createComponentFriends($name) {
		return new FriendsList($this->friendDao, $this->getUser()->id, $this, $name);
	}

	protected function createComponentBlokedUsers($name) {
		return new BlokedUsersList($this->userBlokedDao, $this->getUser()->id, $this, $name);
	}

	protected function createComponentMarkedFromOther($name) {
		return new MarkedFromOther($this->paymentDao, $this->youAreSexyDao, $this->getUser()->id, $this, $name);
	}

	/**
	 * Uloží preferované příspěvky uživatele do streamu.
	 */
	private function fillCorrectDataForStream() {
		if ($this->getUser()->isLoggedIn() && isset($this->userData->property)) {
			$this->initializeStreamUserPreferences();
			$this->dataForStream = $this->streamUserPreferences->getBestStreamItems();
		} else {
			$this->dataForStream = $this->streamDao->getAll("DESC");
		}
	}

	/**
	 * Nastavý preferované příspěvky uživatele.
	 */
	private function initializeStreamUserPreferences() {
		$session = $this->getSession();
		$this->streamUserPreferences = new StreamUserPreferences($this->userData, $this->userDao, $this->streamDao, $this->userCategoryDao, $session);
		//$this->streamUserPreferences->calculate();
	}

}
