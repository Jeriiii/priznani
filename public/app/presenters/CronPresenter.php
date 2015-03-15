<?php

/**
 * Vydává schválená přiznání.
 */
use POS\Model\UserPropertyDao;

class CronPresenter extends BasePresenter {

	private $dataToDebug;

	/** @var \POS\Model\ConfessionDao @inject */
	public $confessionDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	public function startup() {
		parent::startup();

		$this->setLayout("simpleLayout");
	}

	public function actionRecalculateUsersMarks() {
		$users = $this->userDao->getAll();

		//$this->userDao->begginTransaction();

		foreach ($users as $user) {
			$property = $user->property;
			$score = $this->youAreSexyDao->countToUser($user->id, TRUE);
			if (!empty($property)) {
				$this->userPropertyDao->update($property->id, array(
					UserPropertyDao::COLUMN_SCORE => $score
				));
			}
		}

		//$this->userDao->endTransaction();

		echo "Proběhlo přepočítání nálepek";
		die();
	}

	public function actionReleaseConfessions() {
//		$allConCount = $this->confessionDao->countReleaseNotStream();

		$limit = 15;
		$confessions = $this->confessionDao->getToRelease($limit);

		$now = new \Nette\DateTime();

		$counter = 1;

		$this->confessionDao->begginTransaction();

		foreach ($confessions as $con) {
			$this->streamDao->addNewConfession($con->id, $now);
			$counter ++;
		}

		$this->confessionDao->setAsRelease($confessions);

		$this->confessionDao->endTransaction();

//		echo("Nahrávání proběhlo úspěšně, bylo nahráno " . --$counter . " přiznání z " . $allConCount);
		die();
//		$this->redirect("this");
	}

}
