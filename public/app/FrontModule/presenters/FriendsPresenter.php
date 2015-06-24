<?php

/**
 * ActivitiesPresenter Presenter pro práci s přáteli
 */
use POSComponent\UsersList\FriendRequestList;
use POSComponent\UsersList\FriendsList;
use POSComponent\UsersList\SexyList\MarkedFromOther;

class FriendsPresenter extends BasePresenter {

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

	/** @var \POS\Model\FriendDao @inject */
	public $friendDao;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	public function actionDefault() {

	}

	public function renderDefault() {

	}

	public function actionRequests() {

	}

	public function renderRequests() {

	}

	public function actionList() {

	}

	public function renderList() {

	}

	protected function createComponentFriendRequest($name) {
		$sessionManager = $this->getSessionManager();
		$smDaoBox = new DaoBox();
		$smDaoBox->userDao = $this->userDao;
		$smDaoBox->streamDao = $this->streamDao;
		$smDaoBox->userCategoryDao = $this->userCategoryDao;

		return new FriendRequestList($this->friendRequestDao, $this->getUser()->id, $sessionManager, $smDaoBox, $this, $name, TRUE);
	}

	protected function createComponentFriendList($name) {
		return new FriendsList($this->friendDao, $this->getUser()->id, $this, $name, TRUE);
	}

	protected function createComponentSexyForThemList($name) {
		return new MarkedFromOther($this->paymentDao, $this->youAreSexyDao, $this->getUser()->id, $this, $name, TRUE);
	}

}
