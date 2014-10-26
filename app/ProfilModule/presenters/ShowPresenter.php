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
use POSComponent\UsersList\SexyList\IMarked;

class ShowPresenter extends ProfilBasePresenter {

	/**
	 * @var int ID uživatele, jehož profil je zobrazován
	 */
	private $userID;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\CoupleDao
	 * @inject
	 */
	public $coupleDao;

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
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\FriendRequestDao
	 * @inject
	 */
	public $friendRequestDao;

	/**
	 * @var \POS\Model\YouAreSexyDao
	 * @inject
	 */
	public $youAreSexyDao;

	/**
	 * @var \POS\Model\FriendDao
	 * @inject
	 */
	public $friendDao;

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
	 * @var \POS\Model\UserAllowedDao
	 * @inject
	 */
	public $userAllowedDao;

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
	public $dataForStream;

	/**
	 * metoda nastavuje hodnoty predavanych parametru predtim, nez se sablona s uzivatelskym streamem vykresli.
	 * Tyto hodnoty pak predava komponente Stream
	 * @param type $id
	 */
	public function actionDefault($id) {

		if (empty($id)) {
			$id = $this->getUser()->getId();
			if (!$this->userDao->find($id)->property) {
				$this->flashMessage("Nejdříve si vyplňte informace o sobě.");
				$this->redirect(":DatingRegistration:");
			}
		} else {
			if (!$this->userDao->find($id)->property) {
				$this->flashMessage("Tento profil neexistuje, nebo uživatel nemá dokončený profil.");
				$this->redirect(":OnePage:");
			}
		}

		$this->userID = $id;
		$this->dataForStream = $this->streamDao->getUserStreamPosts($id);
	}

	/**
	 * vykresluje  uzivatelsky stream (zed s vlastnimi prispevky)
	 * @param type $id
	 */
	public function renderDefault($id) {

		$user = $this->userDao->find($this->userID);

		$this->template->userData = $user;
		$this->template->userID = $this->userID;
		$this->template->count = $this->dataForStream->count("id");

		$profileGalleryID = $this->userGalleryDao->findProfileGallery($this->userID);
		$profilePhoto = $this->userImageDao->getInGallery($profileGalleryID)->fetch();
		if ($profilePhoto) {
			$this->template->hasProfilePhoto = true;
			$this->template->profilePhoto = $profilePhoto;
		} else {
			$this->template->hasProfilePhoto = false;
		}
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

	protected function createComponentUserInfo($name) {
		return new UserInfo($this->userDao, $this, $name);
	}

	/**
	 * Vykresluje uzivatelsky stream, respektive jeho prispevky k hlavnimu streamu
	 * @return \ProfilStream
	 */
	protected function createComponentProfilStream() {
		return new ProfilStream($this->dataForStream, $this->likeStatusDao, $this->imageLikesDao, $this->userDao, $this->userGalleryDao, $this->userImageDao, $this->confessionDao, $this->streamDao, $this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->likeCommentDao, $this->commentImagesDao, $this->likeStatusCommentDao, $this->commentStatusesDao);
	}

	/**
	 * vykresluje vsechny galerie daneho uzivatele
	 * @return \POSComponent\Galleries\UserGalleries\UserGalleries
	 */
	public function createComponentUserGalleries() {
		$session = $this->getSession();
		$section = $session->getSection('galleriesAccess');

		return new UserGalleriesThumbnails($this->userDao, $this->userGalleryDao, $this->userAllowedDao, $this->friendDao, $section);
	}

	/**
	 * vykresluje obrázky ze všech galerií daného uživatele
	 */
	protected function createComponentUserImagesAll() {
		$images = $this->userImageDao->getAllFromUser($this->userID);

		return new UserGalleryImagesThumbnails($images, $this->userDao);
	}

	/**
	 * formulář pro nahrávání profilových fotografií
	 * @param type $name
	 * @return \Nette\Application\UI\Form\ProfilePhotoUploadForm
	 */
	protected function createComponentUploadPhotoForm($name) {
		return new Frm\ProfilePhotoUploadForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
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
			'stream.js',
			'chat/popup.js',
			'slimbox2.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new \JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	protected function createComponentSendFriendRequest($name) {
		$userIDFrom = $this->userID;
		$userIDTo = $this->getUser()->id;

		return new SendFriendRequest($this->friendRequestDao, $userIDFrom, $userIDTo, $this, $name);
	}

	protected function createComponentYouAreSexy($name) {
		$userIDFrom = $this->userID;
		$userIDTo = $this->getUser()->id;

		return new YouAreSexy($this->youAreSexyDao, $userIDFrom, $userIDTo, $this, $name);
	}

	protected function createComponentFriendsList($name) {
		return new FriendsList($this->friendDao, $this->userID, $this, $name);
	}

	protected function createComponentSexyListIMarked($name) {
		return new IMarked($this->youAreSexyDao, $this->userID, $this, $name);
	}

}
