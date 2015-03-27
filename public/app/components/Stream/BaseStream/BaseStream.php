<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Základ pro stream komponenty - nefunguje samostatně.
 *
 * @author Mario
 */

namespace POSComponent\Stream\BaseStream;

use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;
use POS\Model\UserDao;
use POS\Model\ImageLikesDao;
use POS\Model\LikeStatusDao;
use POS\Model\UserPositionDao;
use POS\Model\EnumPositionDao;
use POS\Model\EnumPlaceDao;
use POS\Model\UserPlaceDao;
use POSComponent\BaseProjectControl;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Form as Frm;
use POS\Model\StreamDao;
use POS\Model\LikeImageCommentDao;
use POS\Model\CommentImagesDao;
use POS\Model\LikeStatusCommentDao;
use POS\Model\CommentStatusesDao;
use POS\Model\LikeConfessionCommentDao;
use POS\Model\CommentConfessionsDao;
use POS\Model\LikeConfessionDao;
use POSComponent\Comments\ConfessionComments;
use POSComponent\Comments\ImageComments;
use POSComponent\Comments\StatusComments;
use POSComponent\BaseLikes\ImageLikes;
use POSComponent\BaseLikes\ConfessionLikes;
use POSComponent\BaseLikes\StatusLikes;
use POSComponent\CropImageUpload\CropImageUpload;
use Polly;

class BaseStream extends BaseProjectControl {

	/** @var Nette\Database\Table\Selection */
	protected $dataForStream;

	/** @var int Jaké příspěvky se mají načítat z DB - posun od posledního vydaného příspěvku */
	protected $offset = null;

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\ImageGalleryDao */
	public $userImageDao;

	/** @var \POS\Model\ConfessionDao */
	public $confessionDao;

	/** @var \POS\Model\ImageLikesDao */
	public $imageLikesDao;

	/** @var \POS\Model\LikeStatusDao */
	public $likeStatusDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var \POS\Model\UserPositionDao */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao */
	public $enumPlaceDao;

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\LikeImageCommentDao */
	public $likeImageCommentDao;

	/** @var \POS\Model\CommentImagesDao */
	public $commentImagesDao;

	/** @var \POS\Model\LikeStatusCommentDao */
	public $likeStatusCommentDao;

	/** @var \POS\Model\CommentStatusesDao */
	public $commentStatusesDao;

	/** @var \POS\Model\LikeConfessionCommentDao */
	public $likeConfessionCommentDao;

	/** @var \POS\Model\CommentConfessionsDao */
	public $commentConfessionsDao;

	/** @var ArrayHash|ActiveRow */
	public $loggedUser;

	/** @var \POS\Model\LikeConfessionDao */
	public $likeConfessionDao;

	public function __construct($data, LikeStatusDao $likeStatusDao, ImageLikesDao $imageLikesDao, UserDao $userDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, ConfessionDao $confDao, StreamDao $streamDao, UserPositionDao $userPositionDao, EnumPositionDao $enumPositionDao, UserPlaceDao $userPlaceDao, EnumPlaceDao $enumPlaceDao, LikeImageCommentDao $likeImageCommentDao, CommentImagesDao $commentImagesDao, LikeStatusCommentDao $likeStatusCommentDao, CommentStatusesDao $commentStatusesDao, LikeConfessionCommentDao $likeConfessionCommentDao, CommentConfessionsDao $commentConfessionsDao, LikeConfessionDao $likeConfessionDao, $loggedUser) {
		parent::__construct();
		$this->dataForStream = $data;
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->confessionDao = $confDao;
		$this->imageLikesDao = $imageLikesDao;
		$this->likeStatusDao = $likeStatusDao;
		$this->streamDao = $streamDao;
		$this->userDao = $userDao;
		$this->userPositionDao = $userPositionDao;
		$this->enumPositionDao = $enumPositionDao;
		$this->userPlaceDao = $userPlaceDao;
		$this->enumPlaceDao = $enumPlaceDao;
		$this->likeImageCommentDao = $likeImageCommentDao;
		$this->commentImagesDao = $commentImagesDao;
		$this->likeStatusCommentDao = $likeStatusCommentDao;
		$this->commentStatusesDao = $commentStatusesDao;
		$this->commentConfessionsDao = $commentConfessionsDao;
		$this->likeConfessionCommentDao = $likeConfessionCommentDao;
		$this->likeConfessionDao = $likeConfessionDao;
		$this->loggedUser = $loggedUser;
	}

	/**
	 * vykresli zaklad pro stream (zed s prispevky) a rozhodne se pro jednou z moznosti - globalni stream nebo uzivatelsky profil stream
	 * @param type $mode
	 * @param type $templateName
	 */
	public function renderBase($mode, $templateName = "baseStream.latte") {
		if ($mode == "mainStream") {
			$this->renderMainStream($templateName);
		}

		if ($mode == "profilStream") {
			$this->renderProfileStream($templateName);
		}

		/* zda-li zobrazit dotaz na blíbenou polohu nebo pozici */
		$user = $this->presenter->user;
		if ($user->isLoggedIn()) {
			$userData = $this->loggedUser;
			// Data ohledně profilového fota a jestli zobrazit/nezobrazit formulář
			$this->template->profilePhoto = $userData->profilFotoID;
		}
		if ($this->getDeviceDetector()->isMobile()) {
			$this->template->mobile = TRUE;
		} else {
			$this->template->mobile = FALSE;
		}

		/* pro určování přístupu do public / private galerií */
		$this->template->userGalleryDao = $this->userGalleryDao;
		$this->template->render();
	}

	/**
	 * Vykresli globalni stream - activity stream.
	 * @param string $templateName Jméno šablony.
	 */
	private function renderMainStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * Vykresli stream na profilu.
	 * @param string $templateName Jméno šablony.
	 */
	private function renderProfileStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * Metoda nastavuje novy offset pro nacitani dalsich prispevku uzivatele
	 */
	public function setNewOffset() {
		/* musí se nastavit i v jQuery pluginu */
		$limit = 4;
		if (!empty($this->offset)) {
			$this->template->stream = $this->dataForStream->limit($limit, $this->offset);
		} else {
			$this->template->stream = $this->dataForStream->limit($limit);
		}
	}

	/**
	 * vraci dalsi data do streamu, ktere snippet appenduje
	 * @param int $offset
	 */
	public function handleGetMoreData($offset) {
		$this->offset = $offset;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('posts');
		} else {
			$this->redirect('this');
		}
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
	 * možnost lajknutí uživatelské fotky na streamu
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	protected function createComponentLikeImages() {
		$streamItems = $this->dataForStream;

		return new Multiplier(function ($streamItem) use ($streamItems) {
			$userGallery = $streamItems->offsetGet($streamItem)->userGallery;
			return new ImageLikes($this->imageLikesDao, $userGallery->lastImage, $this->loggedUser->id, $userGallery->userID, $this->dataForStream);
		});
	}

	protected function createComponentPollsControl() {
		$streamItems = $this->dataForStream;
		$confessionDao = $this->confessionDao;

		return new Multiplier(function ($confessionId) use ($streamItems, $confessionDao ) {
			$confession = $streamItems->offsetGet($confessionId)->confession;
			return new Polly($confession, $confessionDao);
		});
	}

	protected function createComponentCommentImages() {
		$streamItems = $this->dataForStream;

		return new Multiplier(function ($streamItem) use ($streamItems) {
			$userGallery = $streamItems->offsetGet($streamItem)->userGallery;
			$imageComments = new ImageComments($this->likeImageCommentDao, $this->commentImagesDao, $userGallery->lastImage, $this->loggedUser, $userGallery->userID, $this->dataForStream);
			$imageComments->setPresenter($this->getPresenter());
			return $imageComments;
		});
	}

	/**
	 * možnost lajknutí uživatelského statusu na streamu
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	protected function createComponentLikeStatus() {
		$streamItems = $this->dataForStream;

		return new Multiplier(function ($streamItem) use ($streamItems) {
			$status = $streamItems->offsetGet($streamItem)->status;
			return new StatusLikes($this->likeStatusDao, $status, $this->loggedUser->id, $status->userID, $this->dataForStream);
		});
	}

	protected function createComponentPlacesAndPositionsForm($name) {
		return new Frm\PlacesAndPositionsForm($this->userPositionDao, $this->enumPositionDao, $this->userPlaceDao, $this->enumPlaceDao, $this->userDao, $this, $name);
	}

	protected function createComponentCommentStatus() {
		$streamItems = $this->dataForStream;

		return new Multiplier(function ($streamItem) use ($streamItems) {
			$status = $streamItems->offsetGet($streamItem)->status;
			$statusComments = new StatusComments($this->likeStatusCommentDao, $this->commentStatusesDao, $status, $this->loggedUser, $status->userID, $this->dataForStream);
			$statusComments->setPresenter($this->getPresenter());
			return $statusComments;
		});
	}

	protected function createComponentCommentConfession() {
		$streamItems = $this->dataForStream;
		$isUserLoggedIn = $this->presenter->user->isLoggedIn();

		return new Multiplier(function ($streamItem) use ($streamItems, $isUserLoggedIn) {
			$confession = $streamItems->offsetGet($streamItem)->confession;
			$confessionComment = new ConfessionComments($this->likeConfessionCommentDao, $this->commentConfessionsDao, $confession, $this->loggedUser);
			$confessionComment->setPresenter($this->getPresenter());
			return $confessionComment;
		});
	}

	protected function createComponentLikeConfession() {
		$streamItems = $this->dataForStream;
		$userID = NULL;
		if (!empty($this->loggedUser)) {
			$userID = $this->loggedUser->id;
		}

		return new Multiplier(function ($streamItem) use ($streamItems, $userID) {
			$confession = $streamItems->offsetGet($streamItem)->confession;
			return new ConfessionLikes($this->likeConfessionDao, $confession, $userID, $this->dataForStream);
		});
	}

}
