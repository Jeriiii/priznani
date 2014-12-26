<?php

/**
 * Vydává schválená přiznání.
 */
use Notify\EmailNotifies;
use POS\Model\UserPropertyDao;
use Notify\EmailsForOldUsers;

class CronPresenter extends BasePresenter {

	private $dataToDebug;

	/** @var \POS\Model\ConfessionDao @inject */
	public $confessionDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\AdviceDao @inject */
	public $adviceDao;

	/** @var \POS\Model\PartyDao @inject */
	public $partyDao;

	/** @var \POS\Model\ChatMessagesDao @inject */
	public $chatMessagesDao;

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\YouAreSexyDao @inject */
	public $youAreSexyDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Model\OldUserDao @inject */
	public $oldUserDao;

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

	public function actionSendNotifies() {
		$activities = $this->activitiesDao->getNotViewedNotSendNotify();
		$messages = $this->chatMessagesDao->getNotReadedNotSendNotify();

		$emailNotifies = new EmailNotifies($this->mailer);
		/* upozornění na aktivity */
		foreach ($activities as $activity) {
			$emailNotifies->addActivity($activity->event_owner, $activity);
		}

		/* upozornění na zprávy */
		foreach ($messages as $message) {
			$emailNotifies->addMessage($message->recipient);
		}

		$emailNotifies->sendEmails();

		$this->activitiesDao->updateSendNotify();
		$this->chatMessagesDao->updateSendNotify();

		echo "Oznámení byly odeslány";
		die();
	}

	/**
	 * Napíše email starším uživatelům z první verze přiznání.
	 */
	public function actionSendEmailOldUsers() {
		$limit = 200;
		$users = $this->oldUserDao->getNoNotify($limit);
		$emailNotifies = new EmailsForOldUsers($this->mailer);

		/* upozornění na aktivity */
		foreach ($users as $user) {
			$emailNotifies->addEmail($user);
		}

		$emailNotifies->sendEmails();

		$this->oldUserDao->updateLimitNotify($users);

		$countNoNotify = $this->oldUserDao->countNoNotify();
		echo "Bylo odesláno " . $users->count() . " oznámení o nové seznamce. $countNoNotify jich ještě čeká.";
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

	public function actionWow() {
		$pripojeni_manius['ip'] = '91.219.244.178';
		$pripojeni_manius['uzivatel'] = 'priznani';
		$pripojeni_manius['heslo'] = 'SJ28XNypF';
		$pripojeni_manius['db'] = 'priznani';
		$spojeni_manius = mysql_connect($pripojeni_manius['ip'], $pripojeni_manius['uzivatel'], $pripojeni_manius['heslo']);
		mysql_select_db($pripojeni_manius['db'], $spojeni_manius);
		mysql_set_charset('utf8', $spojeni_manius);

		$now = new DateTime();
		$morning = $now->setTime(0, 0, 0)->modify('-1 day');
		$now2 = new DateTime();
		$night = $now2->setTime(23, 59, 59)->modify('-1 day');

		$partyConfessions = $this->partyDao->getBetweenRelease($morning, $night);

		$query = "";

		foreach ($partyConfessions as $confession) {
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznanizparby (text, datum) VALUES ('" . addslashes($confession->note) . "', '" . $release_date . "');";
			mysql_query($insert, $spojeni_manius);
			$query = $query . $insert;
		}

		$sexConfessions = $this->confessionDao->getBetweenRelease($morning, $night);

		$query2 = "";

		foreach ($sexConfessions as $confession) {
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznaniosexu (text, datum) VALUES ('" . addslashes($confession->note) . "', '" . $release_date . "');";
			mysql_query($insert, $spojeni_manius);
			$query2 = $query2 . $insert;
		}
		$this->dataToDebug = $query;
		//mysql_query($query, $spojeni_manius);
		mysql_close($spojeni_manius);
	}

	public function renderWow() {
		$this->template->dataToDebug = $this->dataToDebug;
	}

}
