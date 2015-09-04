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
use POSComponent\UsersList\FriendRequestList;
use POSComponent\UsersList\FriendsList;
use POSComponent\UsersList\BlokedUsersList;
use POSComponent\UsersList\SexyList\MarkedFromOther;
use NetteExt\Helper\ShowUserDataHelper;
use NetteExt\DaoBox;
use POSComponent\Stream\StreamInicializator;
use Nette\Application\Responses\JsonResponse;
use UserBlock\UserBlocker;
use POSComponent\CropImageUpload\CropImageUpload;
use POS\Webloaders\OnePageWebloader;
use POSComponent\PhotoRating;
use POS\Ext\SimpleMenu\Menu;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Path\ImagePathCreator;

class OnePagePresenter extends BasePresenter {

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserBlockedDao @inject */
	public $userBlockedDao;

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
	private $userID;

	/*	 * **** počty aktualit v menu ******* */
	public $countVerificationRequests;
	public $countFriendRequests;
	public $countSexy;
	/* mají se zobrazovat pouze přiznání? */
	public $justConfessions = FALSE;

	public function actionDefault($priznani = NULL, $firstSignIn = FALSE /* TRUE když se přihlásí poprvé od registrace */) {
		if (!$this->getUser()->isLoggedIn() && empty($priznani)) {
			$this->redirect(':DatingRegistration:');
		}
		$this->justConfessions = !empty($priznani);
		$this->userID = $this->getUser()->getId();
		$this->intro = $firstSignIn ? 1 : 0;

		/* počty aktualit v menu */
		//$this->countVerificationRequests = $this->verificationPhotoDao->findByUserID($this->user->id)->count();
		if (!$this->deviceDetector->isMobile() && $this->getUser()->isLoggedIn()) {/* pokud nejsem na mobilu, údaje se nepředají */
			$this->countFriendRequests = $this->friendRequestDao->getAllToUser($this->getUser()->id)->count();
			$this->countSexy = $this->youAreSexyDao->countToUser($this->getUser()->id);
		}
	}

	public function renderDefault($priznani = NULL, $firstSignIn = FALSE /* TRUE když se přihlásí poprvé od registrace */) {
		if ($this->user->isLoggedIn()) {
			$this->template->userID = $this->userID;
			$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);
			$this->template->profilePhoto = $this->loggedUser->profilFotoID;
			$this->template->loggedUser = $this->loggedUser;


			$this->template->isUserPaying = $this->paymentDao->isUserPaying($this->userID);

			if ($this->getUser()->isLoggedIn()) {
				$this->template->sexyLabelToolTip = "Hodnost podle počtu - JE SEXY <br />" . ShowUserDataHelper::getLabelInfoText($this->loggedUser->property->type);
			}

			$this->showQuestion();
		}
	}

	public function renderMobileDefault() {
		$this->template->userID = $this->userID;
		$this->template->profileGallery = $this->userGalleryDao->findProfileGallery($this->userID);
		$this->template->loggedUser = $this->loggedUser;

		if ($this->user->isLoggedIn()) {
			$this->showQuestion();
		}
	}

	/**
	 * Vrátí stream ve formátu JSON
	 */
	public function actionStreamInJson($offset = 0) {
		$userStream = $this->createComponentUserStream();
		$data = $userStream->getDataInArray($offset);

		$dataToSend = array();
		foreach ($data as $item) {
			if (!empty($item->userGalleryID)) {
				$this->addImgUrl($item);
			}
			$dataToSend[] = $item;
		}

		$json = new JsonResponse(array("data" => $dataToSend), "application/json; charset=utf-8");
		$this->sendResponse($json);
	}

	/**
	 * Přidá url k obrázku.
	 * @param ArrayHash $item Příspěvek ve streamu obsahující obrázek.
	 */
	public function addImgUrl($item) {
		$image = $item->userGallery->lastImage;
		if (isset($image)) {
			$userID = $this->loggedUser->id;
			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($image->galleryID, $userID);
			$item->imgUrl = ImagePathCreator::getImgScrnPath($image->id, $image->suffix, $galleryFolder, '');
		} else {
			$item->imgUrl = null;
		}
	}

	/**
	 * Vytvoří a vrátí komponentu na hlavní uživatelký stream.
	 * @return UserStream Hlavní uživatelský stream.
	 */
	protected function createComponentUserStream() {
		$session = $this->getSession();
		$streamInicializator = new StreamInicializator();

		$userStream = $streamInicializator->createUserStream($this, $session, $this->user, $this->loggedUser, $this->justConfessions);

		return $userStream;
	}

	public function createComponentJs() {
		return OnePageWebloader::js($this->template);
	}

	public function createComponentSearch() {
		$component = new Search();
		return $component;
	}

	/**
	 * formulář pro nahrávání profilových fotografií
	 * @param type $name
	 * @return \Nette\Application\UI\Form\ProfilePhotoUploadForm
	 */
	protected function createComponentCropImageUpload($name) {
		return new CropImageUpload($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * Zablokuje uživatele.
	 * @param int $blockUserID Id uživatele, který se má blokovat.
	 */
	public function handleBlockUser($blockUserID) {
		$blocker = $this->createUserBloker(); /* zablokuje uživatele */

		$blocker->blockUser($blockUserID, $this->loggedUser, $this->session);

		$this->flashMessage("Uživatel byl zablokován");
		$this->redirect("this");
	}

	/**
	 * Továrnička na třídu pro blokování/odblokování uživatele
	 */
	private function createUserBloker() {
		$daoBox = new DaoBox();

		$daoBox->userDao = $this->userDao;
		$daoBox->streamDao = $this->streamDao;
		$daoBox->userCategoryDao = $this->userCategoryDao;
		$daoBox->userBlockedDao = $this->userBlockedDao;

		$blocker = new UserBlocker($daoBox);

		return $blocker;
	}

	/**
	 * Odblokuje uživatele.
	 */
	public function handleUnblockUser($unblockUserID) {
		$blocker = $this->createUserBloker();

		$blocker->unblockUser($unblockUserID, $this->loggedUser, $this->session);

		$this->flashMessage("Uživatel byl odblokován");
		$this->redirect("this");
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

	/**
	 * Zobrazit dotaz na oblíbenou polohu nebo pozici.
	 */
	private function showQuestion() {
		$userData = $this->loggedUser;
		$userProperty = $userData->property;
		if ($userProperty) { // ochrana proti uživatelům, co nemají vyplněné user property
			$placePosSession = $this->presenter->getSession('placePosSession');
			$placePosSession->count++;
			$this->template->placePosSession = $placePosSession;
			$placePosSession->setExpiration(0, 'password');

			$place = $this->userPlaceDao->isFilled($userProperty->id);
			$position = $this->userPositionDao->isFilled($userProperty->id);

			$this->template->place = $place;
			$this->template->position = $position;
		}
		$this->template->userData = $userData;
		$this->template->newInfo = $this->usersNewsDao->getActual($this->loggedUser->id);
	}

	/**
	 * Přidá fotky do defaultní galerie.
	 * @param string $name
	 * @return \Nette\Application\UI\Form\NewStreamImageForm
	 */
	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * Formulář na výběr pozice a místa sexu.
	 * @param string $name
	 * @return \Nette\Application\UI\Form\PlacesAndPositionsForm
	 */
	protected function createComponentPlacesAndPositionsForm($name) {
		return new Frm\PlacesAndPositionsForm($this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->userDao, $this, $name);
	}

	/**
	 * Přidání přiznání do streamu
	 * @param string $name
	 * @return \Nette\Application\UI\Form\AddItemForm
	 */
	protected function createComponentAddConfessionForm($name) {
		$addItem = new Frm\AddItemForm($this, $name);
		$addItem->setConfession($this->confessionDao);
		return $addItem;
	}

	protected function createComponentFilterForm($name) {
		return new Frm\FilterStreamForm($this, $name);
	}

	protected function createComponentStatusForm($name) {
		return new Frm\AddStatusForm($this->streamDao, $this->statusDao, $this->loggedUser->property, $this, $name);
	}

	protected function createComponentPhotoRating($name) {
		return new PhotoRating($this->userImageDao, $this->rateImageDao, $this->imageLikesDao, $this->loggedUser, $this->userCategoryDao, $this->session, $this, $name);
	}

	protected function createComponentSetInitimityForm($name) {
		$loggedUser = $this->loggedUser;
		if (!($loggedUser instanceof ActiveRow)) {
			$loggedUser = $this->userDao->find($loggedUser->id);
		}

		$daoBox = new DaoBox();
		$daoBox->userDao = $this->userDao;
		$daoBox->streamDao = $this->streamDao;
		$daoBox->userCategoryDao = $this->userCategoryDao;
		$daoBox->paymentDao = $this->paymentDao;
		$daoBox->userImageDao = $this->userImageDao;
		$daoBox->userGalleryDao = $this->userGalleryDao;

		$sm = $this->getSessionManager();
		return new Frm \ SetInitimityForm($loggedUser, $sm, $daoBox, $this, $name);
	}

	protected function createComponentLeftMenu($name, $showOn = Menu::SHOW_ALL_PRES) {
		parent::createComponentLeftMenu($name, Menu::SHOW_ONE_PAGE);
	}

	/** Přečtení novinek (nad streamem) uživatelem */
	public function handleNewReaded($newID) {
		$this->usersNewsDao->deleteByUser($this->loggedUser->id, $newID);
		$this->redirect("this");
	}

}
