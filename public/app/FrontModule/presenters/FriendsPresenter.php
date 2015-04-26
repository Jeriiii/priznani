<?php

/**
 * ActivitiesPresenter Presenter pro práci s přáteli
 */
use POSComponent\UsersList\FriendRequestList;

class FriendsPresenter extends BasePresenter {

	/** @var \POS\Model\FriendRequestDao @inject */
	public $friendRequestDao;

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
		return new FriendRequestList($this->friendRequestDao, $this->getUser()->id, $this, $name, TRUE);
	}

}
