<?php

namespace SafeModule;

use POSComponent\Stream\StreamInicializator;
use AntispamControl;

/**
 * Slouží pro testování.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
class TestsPresenter extends BasePresenter {

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

	/** @var \POS\Model\UsersNewsDao @inject */
	public $usersNewsDao;

	/** @var \POS\Model\RateImageDao @inject */
	public $rateImageDao;

	public function renderDefault() {
		$this->template->qTestPath = $this->template->basePath . '/tests/qunits';
	}

	/** na této stránce je stream bez js */
	public function renderStream() {

	}

	protected function createComponentUserStream() {
		$session = $this->getSession();
		$streamInicializator = new StreamInicializator();

		$storage = new \Nette\Http\UserStorage($session);
		$storage->setIdentity(new \Nette\Security\Identity($id = 1));
		$user = new \Nette\Security\User($storage);
		$user->logout();

		$isLoggedIn = false;

		AntispamControl::register();

		return $streamInicializator->createUserStream($this, $session, $user, $isLoggedIn);
	}

}
