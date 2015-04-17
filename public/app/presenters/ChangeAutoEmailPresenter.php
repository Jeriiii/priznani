<?php

use NetteExt\DataCoder;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserDao;

/**
 * Presenter pro změnu autoemailu - newsletterů
 */
class ChangeAutoEmailPresenter extends BasePresenter {

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var ActiveRow|NULL */
	private $user;

	public function actionSetWeekly($id) {
		$userID = DataCoder::encode($id);
		$user = $this->userDao->find($userID);
		if ($user instanceof ActiveRow) {
			$user->update(array(
				UserDao::COLUMN_EMAIL_PERIOD => UserDao::EMAIL_PERIOD_WEEKLY
			));
		}

		$this->user = $user;
	}

	public function renderSetWeekly() {
		$this->template->userData = $this->user;
	}

}
