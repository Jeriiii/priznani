<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace ProfilModule;

use Nette\Application\UI\Form as Frm;
use POSComponent\Galleries\UserGalleriesThumbnails\UserGalleriesThumbnails;
use POSComponent\Galleries\UserImagesGalleryThumbnails\UserGalleryImagesThumbnails;
use POSComponent\Stream\ProfilStream;
use POSComponent\UserInfo\UserInfo;
use POSComponent\AddToList\SendFriendRequest;
use POSComponent\AddToList\YouAreSexy;
use POSComponent\UsersList\FriendsList;
use POSComponent\UsersList\SexyList\MarkedFromOther;
use POSComponent\CropImageUpload\CropImageUpload;
use NetteExt\DaoBox;
use NetteExt\Helper\ShowUserDataHelper;
use POS\Ext\LastActive;
use POSComponent\Confirm;
use POS\UserPreferences\StreamUserPreferences;
use POS\UserPreferences\SearchUserPreferences;
use UserBlock\UserBlocker;
use POS\Model\ActivitiesDao;

class ShowPresenter extends ProfilBasePresenter {

	/** @var int ID uživatele, jehož profil je zobrazován */
	private $userID;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	/** @var \POS\Model\CoupleDao @inject */
	public $coupleDao;

	/** @var \POS\Model\UserGalleryDao @inject */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao @inject */
	public $userImageDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\ConfessionDao @inject */
	public $confessionDao;

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var \POS\Model\ImageLikesDao @inject */
	public $imageLikesDao;

	/** @var \POS\Model\LikeStatusDao @inject */
	public $likeStatusDao;

	/** @var \POS\Model\UserPositionDao @inject */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao @inject */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao @inject */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao @inject */
	public $enumPlaceDao;

	/** @var \POS\Model\UserAllowedDao @inject */
	public $userAllowedDao;

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
	public $verificationPhotoRequestDao;

	/** @var \POS\Model\EnumVigorDao @inject */
	public $enumVigorDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Model\UserBlockedDao @inject */
	public $userBlockedDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;
	public $dataForStream;

	/** @var \Nette\Database\Table\ActiveRow|\Nette\ArrayHash */
	private $userData;

	/**
	 * metoda nastavuje hodnoty predavanych parametru predtim, nez se sablona s uzivatelskym streamem vykresli.
	 * Tyto hodnoty pak predava komponente Stream
	 * @param type $id
	 */
	public function actionDefault($id) {
		if (empty($id)) {
			/* kontrola, zda se nesnaží nepřihlášený uživatel zobrazit svůj profil */
			if (!$this->getUser()->isLoggedIn()) {
				$this->flashMessage("Pro zobrazení vašeho profilu se nejdříve přihlašte");
				$this->redirect(":Sign:in");
			}
			$id = $this->getUser()->getId();
			if (!$this->userDao->find($id)->property) {
				$this->flashMessage("Nejdříve si vyplňte informace o sobě.");
				$this->redirect(":DatingRegistration:");
			}
			$this->userData = $this->userDao->find($id);
		} else {
			/* Zjistí, zda uživatel nenavštěvuje profil uživatele, který ho zablokoval. */
			$isBlocked = $this->userBlockedDao->isBlocked($id, $this->loggedUser->id);
			if ($isBlocked) {
				$this->flashMessage('Tento uživatel Vás zablokoval.');
				$this->redirect(':OnePage:');
			}

			$user = $this->userDao->find($id);
			if (empty($user) || !$user->property) {
				$this->flashMessage("Tento profil neexistuje, nebo uživatel nemá dokončený profil.");
				$this->redirect(":OnePage:");
			}
			/* kontrola, zda se nesnaží nepřihlášený uživatel zobrazit něčí profil,
			 * časem by se dala zobrazit ještě foto uživatele, kterého chtěl zobrazit */
			if (!$this->getUser()->isLoggedIn()) {
				$this->flashMessage("Pro zobrazení profilu $user->user_name se nejdříve přihlašte");
				$this->redirect(":Sign:in");
			}
			$this->userData = $user;
		}

		$this->userID = $id;
		$this->dataForStream = $this->streamDao->getUserStreamPosts($id);
	}

	/**
	 * vykresluje  uzivatelsky stream (zed s vlastnimi prispevky)
	 * @param type $id
	 */
	public function renderDefault($id, $weAreSorry = FALSE) {
		/* kontrola zda jde o muj profil */
		$isMyProfile = FALSE;
		if ($this->user->isLoggedIn()) {
			if ($this->userID == $this->getPresenter()->getUser()->id) {
				$isMyProfile = TRUE;
			}
		}
		if ($weAreSorry) {
			$this->template->showSorryMessage = TRUE;
		}
		$this->template->isMyProfile = $isMyProfile;
		$this->template->isBlocked = $this->userBlockedDao->isBlocked($this->loggedUser->id, $id);

		$verificationAsked = $this->verificationPhotoRequestDao->findByUserID2($this->userID);

		if ($verificationAsked->fetch()) {
			$this->template->asked = TRUE;
		} else {
			$this->template->asked = FALSE;
		}
		if (!empty($id)) {
			$user = $this->userDao->find($id);
		} else {
			$user = $this->userData;
		}

		$this->template->userData = $user;
		$this->template->userID = $this->userID;
		$lastActive = LastActive::format($user->last_active);
		$this->template->lastActive = $lastActive;
		$this->template->count = $this->dataForStream->count("id");
		$this->template->isFriends = $this->friendDao->isFriend($id, $this->getUser()->id);
		$this->template->sexyLabelToolTip = "Hodnost podle počtu - JE SEXY <br />" . ShowUserDataHelper::getLabelInfoText($user->property->type);

		$profileGalleryID = $this->userGalleryDao->findProfileGallery($this->userID);
		$profilePhoto = $this->userImageDao->getInGallery($profileGalleryID)->fetch();
		if ($profilePhoto) {
			$this->template->hasProfilePhoto = true;
			$this->template->profilePhoto = $profilePhoto;
		} else {
			$this->template->hasProfilePhoto = false;
		}
		$this->template->vigor = $this->getVigor($user->property->age);
	}

	/**
	 * Zablokuje uživatele.
	 * @param int $blockUserID Id uživatele, který se má blokovat.
	 */
	public function handleBlockUser($blockUserID) {
		$blocker = $this->createUserBloker(); /* zablokuje uživatele */
		$blocker->blockUser($blockUserID, $this->loggedUser, $this->session);

		$this->flashMessage("Uživatel byl zablokován");
		$this->redirect("this", array('weAreSorry' => TRUE));
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
	 * vykresluje  uzivatelsky stream (zed s vlastnimi prispevky)
	 * @param type $id
	 */
	public function renderMobileDefault($id) {
		/* kontrola zda jde o muj profil */
		$isMyProfile = FALSE;
		if ($this->user->isLoggedIn()) {
			if ($this->userID == $this->getPresenter()->getUser()->id) {
				$isMyProfile = TRUE;
			}
		}
		$this->template->isMyProfile = $isMyProfile;
		$this->template->isBlocked = $this->userBlockedDao->isBlocked($this->loggedUser->id, $id);


		$verificationAsked = $this->verificationPhotoRequestDao->findByUserID2($this->userID);

		if ($verificationAsked->fetch()) {
			$this->template->asked = TRUE;
		} else {
			$this->template->asked = FALSE;
		}
		if (!empty($id)) {
			$user = $this->userDao->find($id);
		} else {
			$user = $this->userData;
		}

		$this->template->userData = $user;
		$this->template->userID = $this->userID;
		$lastActive = LastActive::format($user->last_active);
		$this->template->lastActive = $lastActive;
		$this->template->count = $this->dataForStream->count("id");
		$this->template->isFriends = $this->friendDao->isFriend($id, $this->getUser()->id);
		$this->template->sexyLabelToolTip = "Hodnost podle počtu - JE SEXY <br />" . ShowUserDataHelper::getLabelInfoText($user->property->type);

		$profileGalleryID = $this->userGalleryDao->findProfileGallery($this->userID);
		$profilePhoto = $this->userImageDao->getInGallery($profileGalleryID)->fetch();
		if ($profilePhoto) {
			$this->template->hasProfilePhoto = true;
			$this->template->profilePhoto = $profilePhoto;
		} else {
			$this->template->hasProfilePhoto = false;
		}
		$this->template->vigor = $this->getVigor($user->property->age);
	}

	private function getVigor($age) {
		Frm\DatingRegistrationBaseForm::getVigor($age);
	}

	public function actionUserImages($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$this->userID = $id;
	}

	/**
	 * vykresluje template se vsemi fotky uzivateli
	 * @param type $id
	 */
	public function renderUserImages($id) {
		$user = $this->userDao->find($this->userID);

		$this->template->userData = $user;
		$this->template->userID = $this->userID;
	}

	/**
	 * vykresluje informace uzivatele, pripadne partnerovi
	 * @param type $id
	 */
	public function renderUserInfo($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}

		$this->userID = $id;
		$this->template->userID = $id;

		$user = $this->userDao->find($id);
		$userProperty = $this->userDao->findProperties($id);

		$this->template->userData = $user;

		$property = $userProperty->type;
		if ($property == 3 || $property == 4 || $property == 5) {
			$this->template->userPartnerProfile = $this->coupleDao->getPartnerData($user->coupleID);
		}
		$this->template->mode = "listAll";
	}

	public function renderVerification() {
		$this->template->verificationGallery = $this->userGalleryDao->findVerificationGalleryByUser($this->user->id);
		$this->template->requests = $this->verificationPhotoRequestDao->findByUserID($this->user->id);
	}

	protected function createComponentUserInfo($name) {
		return new UserInfo($this->userDao, $this, $name);
	}

	public function handleAcceptUser($userID) {
		$gallery = $this->userGalleryDao->findVerificationGalleryByUser($this->user->id);
		$this->userAllowedDao->insertData($userID, $gallery->id);
		$this->verificationPhotoRequestDao->acceptRequest($userID);
		$this->activitiesDao->createImageActivity($this->user->id, $userID, $gallery->lastImage, ActivitiesDao::TYPE_VERIF_PHOTO_ACCEPT);
		if ($this->isAjax()) {
			$this->redrawControl('requests');
		} else {

			$this->redirect("this");
		}
	}

	public function handleRejectUser($userID) {
		$gallery = $this->userGalleryDao->findVerificationGalleryByUser($this->user->id);
		$this->verificationPhotoRequestDao->rejectRequest($userID);
		$this->activitiesDao->createImageActivity($this->user->id, $userID, $gallery->lastImage, ActivitiesDao::TYPE_VERIF_PHOTO_REJECT);
		if ($this->isAjax()) {
			$this->redrawControl('requests');
		} else {

			$this->redirect("this");
		}
	}

	/**
	 * Vykresluje uzivatelsky stream, respektive jeho prispevky k hlavnimu streamu
	 * @return \ProfilStream
	 */
	protected function createComponentProfilStream() {
		$daoBox = $this->getDaoBoxProfilStream();

		return new ProfilStream($this->dataForStream, $daoBox, $this->loggedUser);
	}

	protected function createComponentBlockUser($name) {
		$blockUser = new Confirm($this, $name);
		$blockUser->setTittle("Blokovat uživatele");
		$blockUser->setMessage("Opravdu chcete zablokovat tohoto uživatele?");
		$blockUser->setBtnText("BLOKOVAT UŽIVATELE");
		if (!$this->deviceDetector->isMobile()) {
			$blockUser->setBtnClass('profile-btn blockUserBtn');
		} else {
			$blockUser->setBtnClass('ui-btn');
		}
		return $blockUser;
	}

	/**
	 * Vrátí DaoBox naplněný pro user stream.
	 */
	private function getDaoBoxProfilStream() {
		$daoBox = new DaoBox;

		$daoBox->likeStatusDao = $this->likeStatusDao;
		$daoBox->imageLikesDao = $this->imageLikesDao;
		$daoBox->userDao = $this->userDao;
		$daoBox->likeConfessionDao = $this->likeConfessionDao;

		$daoBox->streamDao = $this->streamDao;
		$daoBox->userGalleryDao = $this->userGalleryDao;
		$daoBox->userImageDao = $this->userImageDao;
		$daoBox->confessionDao = $this->confessionDao;

		$daoBox->likeImageCommentDao = $this->likeImageCommentDao;
		$daoBox->commentImagesDao = $this->commentImagesDao;
		$daoBox->likeStatusCommentDao = $this->likeStatusCommentDao;
		$daoBox->commentStatusesDao = $this->commentStatusesDao;

		$daoBox->likeConfessionCommentDao = $this->likeConfessionCommentDao;
		$daoBox->commentConfessionsDao = $this->commentConfessionsDao;

		return $daoBox;
	}

	/**
	 * vykresluje vsechny galerie daneho uzivatele
	 * @return \POSComponent\Galleries\UserGalleries\UserGalleries
	 */
	public function createComponentUserGalleries() {
		return new UserGalleriesThumbnails($this->userDao, $this->userGalleryDao, $this->userAllowedDao, $this->friendDao);
	}

	/**
	 * vykresluje obrázky ze všech galerií daného uživatele
	 */
	protected function createComponentUserImagesAll() {
		$images = $this->userImageDao->getAllFromUser($this->userID, $this->loggedUser->id, $this->userGalleryDao);

		return new UserGalleryImagesThumbnails($images, $this->userDao);
	}

	/**
	 * cropovací formulář pro nahrávání profilových fotografií
	 * @param type $name
	 * @return CropImageUpload
	 */
	protected function createComponentUploadPhoto($name) {
		return new CropImageUpload($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this->imageUploader, $this, $name);
	}

	/**
	 * formulář pro poslání zprávy
	 * @param type $name
	 * @return Frm\SendMessageForm
	 */
	protected function createComponentSendMessageForm($name) {
		return new Frm\SendMessageForm($this->chatManager, $this->userID, $this, $name);
	}

	/**
	 * WebLoader pro minifikace skriptu
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new \JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	protected function createComponentSendFriendRequest($name) {
		$userIDFrom = $this->getUser()->id;
		$userIDTo = $this->userID;

		return new SendFriendRequest($this->activitiesDao, $this->friendRequestDao, $userIDFrom, $userIDTo, $this, $name);
	}

	protected function createComponentYouAreSexy($name) {
		$userIDFrom = $this->getUser()->id;
		$userIDTo = $this->userID;

		return new YouAreSexy($this->youAreSexyDao, $this->userPropertyDao, $userIDFrom, $userIDTo, $this, $name);
	}

	protected function createComponentFriendsList($name) {
		return new FriendsList($this->friendDao, $this->userID, $this, $name);
	}

	protected function createComponentSexyListMarkedFromOther($name) {
		return new MarkedFromOther($this->paymentDao, $this->youAreSexyDao, $this->userID, $this, $name);
	}

	public function handleRequestConfirmPhoto($id, $viewerID) {
		$this->verificationPhotoRequestDao->createRequest($id, $viewerID);
		$this->flashMessage("žádost o ověřovací fotku podána.");
		$this->redirect("this");
	}

}
