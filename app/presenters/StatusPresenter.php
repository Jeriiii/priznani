<?php

use POSComponent\BaseLikes\StatusLikes;
use POS\Model\LikeStatusDao;
use POS\Model\LikeStatusCommentDao;
use POS\Model\CommentStatusesDao;
use POSComponent\Comments\StatusComments;

/**
 * TempPresenter Description
 */
class StatusPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\StatusDao
	 * @inject
	 */
	public $statusDao;

	/**
	 * @var \POS\Model\LikeStatusDao
	 * @inject
	 */
	public $likeStatusDao;

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
	 * @var ActiveRow|bool Status
	 */
	private $status;

	public function startup() {
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect("Sign:in");
			$this->flashMessage("Pro zobrazení statusu se nejdříve přihlašte");
		}
	}

	public function actionDefault($id) {
		$this->status = $this->statusDao->find($id);
		if (empty($this->status)) {
			$this->redirect("OnePage:");
			$this->flashMessage("Tento status neexistuje, nebo byl smazán.");
		}
	}

	public function renderDefault($id) {
		$this->template->status = $this->status;
	}

	protected function createComponentLikes() {
		$status = $this->status;
		return new StatusLikes($this->likeStatusDao, $status, $this->loggedUser->id, $status->userID);
	}

	protected function createComponentComments() {
		$status = $this->status;
		$statusComments = new StatusComments($this->likeStatusCommentDao, $this->commentStatusesDao, $status, $this->loggedUser, $status->userID);
		$statusComments->setPresenter($this);
		return $statusComments;
	}

}
