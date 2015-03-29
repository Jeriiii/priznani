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
use NetteExt\Helper\ShowUserDataHelper;
use NetteExt\DaoBox;

class OnePagePresenter extends BasePresenter {

	/**
	 * Vybere do streamu nejvhodnější data vzhledem k preferencím daného uživatele
	 * @var \POS\UserPreferences\StreamUserPreferences
	 */
	public $streamUserPreferences;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserBlokedDao @inject */
	public $userBlokedDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	/** @var \POS\Model\ConfessionDao @inject */
	public $confessionDao;

	/** @var \POS\Model\StatusDao @inject */
	public $statusDao;

	/** @var \POS\Model\ImageLikesDao @inject */
	public $imageLikesDao;

	/** @var \POS\Model\LikeStatusDao @inject */
	public $likeStatusDao;

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	/** @var \POS\Model\UserPositionDao @inject */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao @inject */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao @inject */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao @inject */
	public $enumPlaceDao;

	/** @var \POS\Model\LikeImageCommentDao @inject */
	public $likeImageCommentDao;

	/** @var \POS\Model\CommentImagesDao @inject */
	public $commentImagesDao;

	/** @var \POS\Model\LikeStatusCommentDao @inject */
	public $likeStatusCommentDao;

	/** @var \POS\Model\CommentStatusesDao @inject */
	public $commentStatusesDao;

	/** @var \POS\Model\LikeConfessionCommentDao @inject */
	public $likeConfessionCommentDao;

	/** @var \POS\Model\CommentConfessionsDao @inject */
	public $commentConfessionsDao;

	/** @var \POS\Model\LikeConfessionDao @inject */
	public $likeConfessionDao;

	/** @var \POS\Model\VerificationPhotoRequestsDao @inject */
	public $verificationPhotoDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	/** @var \POS\Model\UsersNewsDao @inject */
	public $usersNewsDao;

	/** @var \POS\Model\RateImageDao @inject */
	public $rateImageDao;

	/** @var \Nette\Database\Table\Selection Všechny příspěvky streamu. */
	public $dataForStream;
	private $userID;

	public function actionDefault() {
		$this->userID = $this->getUser()->getId();
		$this->fillCorrectDataForStream();
	}

	public function renderDefault() {
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);
		$this->template->loggedUser = $this->loggedUser;

		$this->template->countFriendRequests = $this->friendRequestDao->getAllToUser($this->userID)->count();
		$this->template->countSexy = $this->youAreSexyDao->countToUser($this->userID);
		$this->template->isUserPaying = $this->paymentDao->isUserPaying($this->userID);
		$this->template->countVerificationRequests = $this->verificationPhotoDao->findByUserID($this->user->id)->count();
		if ($this->getUser()->isLoggedIn()) {
			$this->template->sexyLabelToolTip = "Hodnost podle počtu - JE SEXY <br />" . ShowUserDataHelper::getLabelInfoText($this->loggedUser->property->type);
		}
	}

	protected function createComponentUserStream() {
		$daoBox = $this->getDaoBoxUserStream();
		$session = $this->getSession();

		return new UserStream($this->dataForStream, $daoBox, $session, $this->loggedUser);
	}

	/**
	 * Vrátí DaoBox naplněný pro user stream.
	 */
	private function getDaoBoxUserStream() {
		$daoBox = new DaoBox;
		$daoBox->likeStatusDao = $this->likeStatusDao;
		$daoBox->imageLikesDao = $this->imageLikesDao;
		$daoBox->userDao = $this->userDao;
		$daoBox->statusDao = $this->statusDao;

		$daoBox->streamDao = $this->streamDao;
		$daoBox->userGalleryDao = $this->userGalleryDao;
		$daoBox->userImageDao = $this->userImageDao;
		$daoBox->confessionDao = $this->confessionDao;

		$daoBox->userPositionDao = $this->userPositionDao;
		$daoBox->enumPositionDao = $this->enumPositionDao;
		$daoBox->userPlaceDao = $this->userPlaceDao;
		$daoBox->enumPlaceDao = $this->enumPlaceDao;

		$daoBox->likeImageCommentDao = $this->likeImageCommentDao;
		$daoBox->commentImagesDao = $this->commentImagesDao;
		$daoBox->likeStatusCommentDao = $this->likeStatusCommentDao;
		$daoBox->commentStatusesDao = $this->commentStatusesDao;

		$daoBox->likeConfessionCommentDao = $this->likeConfessionCommentDao;
		$daoBox->commentConfessionsDao = $this->commentConfessionsDao;
		$daoBox->likeConfessionDao = $this->likeConfessionDao;
		$daoBox->usersNewsDao = $this->usersNewsDao;

		$daoBox->rateImageDao = $this->rateImageDao;
		$daoBox->imageLikesDao = $this->imageLikesDao;
		$daoBox->userCategoryDao = $this->userCategoryDao;

		return $daoBox;
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'profilePhotoBackground.js',
			'stream.js',
			'lists/initFriendRequest.js',
			'lists/initFriends.js',
			/* 'lists/initBlokedUsers.js', */ //zakomentováno do první verze přiznání
			'lists/initMarkedFromOther.js',
			'onepage/default.js'
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
		return new \POSComponent\Search\BestMatchSearch($this->loggedUser, $this->userDao, $this->userCategoryDao, $session, $this, $name);
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
		if ($this->getUser()->isLoggedIn() && isset($this->loggedUser->property)) {
			$this->initializeStreamUserPreferences();
			$this->dataForStream = $this->streamUserPreferences->getBestStreamItems();
		} else {
			$this->dataForStream = $this->streamDao->getForUnloggedUser("DESC");
		}
	}

	/**
	 * Nastavý preferované příspěvky uživatele.
	 */
	private function initializeStreamUserPreferences() {
		$session = $this->getSession();
		$this->streamUserPreferences = new StreamUserPreferences($this->loggedUser, $this->userDao, $this->streamDao, $this->userCategoryDao, $session);
	}

}
