<?php

use Notify\CronNotifies;
use Notify\CronForOldUsers;
use Nette\Application\Responses\JsonResponse;

/**
 * Crony které se zabývají pouze emaily
 */
class CronEmailPresenter extends BasePresenter {

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

	/** @var \POS\Model\OldUserDao @inject */
	public $oldUserDao;

	public function startup() {
		parent::startup();

		$this->setLayout("simpleLayout");
	}

	/**
	 * Zobrazuje JSON s emaily o aktivitě uživatelů.
	 * @param string $userName
	 * @param string $userPassword
	 */
	public function actionMailToJSON($userName, $userPassword) {
		$this->checkAccess($userName, $userPassword);
		$setWeeklyLink = $this->getLinkNotifySetWeekly();

		$cronNotifies = new CronNotifies($this->activitiesDao, $this->chatMessagesDao, $setWeeklyLink);
		$messages = $cronNotifies->getEmails();

		$json = new JsonResponse($messages, "application/json; charset=utf-8");
		$this->sendResponse($json);
	}

	public function actionMailIsSended($userName, $userPassword) {
		$this->checkAccess($userName, $userPassword);
		$setWeeklyLink = $this->getLinkNotifySetWeekly();

		$cronNotifies = new CronNotifies($this->activitiesDao, $this->chatMessagesDao, $setWeeklyLink);
		$cronNotifies->markEmailsLikeSended($this->userDao);
	}

	/**
	 * Odesílá emaily o nedávné aktivitě uživatelů.
	 */
	public function actionSendNotifies() {
		$setWeeklyLink = $this->getLinkNotifySetWeekly();
		$cronNotifies = new CronNotifies($this->activitiesDao, $this->chatMessagesDao, $setWeeklyLink);

		$cronNotifies->sendEmails($this->mailer);
		$cronNotifies->markEmailsLikeSended($this->userDao);

		echo "Oznámení byly odeslány";
		die();
	}

	/**
	 * Vrátí neúplný link kterým se dá změnit posílání informačních emailů na týdenní. Neúplný je o dokódované
	 * id uživatele.
	 */
	private function getLinkNotifySetWeekly() {
		return $this->link('//ChangeAutoEmail:setWeekly');
	}

	/**
	 * Zobrazí JSON s emaily starším uživatelům z první verze přiznání.
	 * @param string $userName
	 * @param string $userPassword
	 */
	public function actionMailToOldUsersJson($userName, $userPassword) {
		$this->checkAccess($userName, $userPassword);

		$cronForOldUsers = new CronForOldUsers($this->oldUserDao);
		$messages = $cronForOldUsers->getEmails();

		$json = new JsonResponse($messages, "application/json; charset=utf-8");
		$this->sendResponse($json);
	}

	/**
	 * Označí emaily které se měli odeslat starším uživatelům z první verze přiznání jako přečtené.
	 */
	public function actionMailOldUsersIsSended($userName, $userPassword) {
		$this->checkAccess($userName, $userPassword);

		$cronForOldUsers = new CronForOldUsers($this->oldUserDao);
		$cronForOldUsers->markEmailsLikeSended();
	}

	/**
	 * Kontrola uživatele, který ke stránce přistupuje.
	 */
	public function checkAccess($userName, $userPassword) {
		if ($userName != 'mailuser' || $userPassword != 'a10b06001') {
			$message = array(
				'access' => 'denied',
				'access2' => 'denied',
				'access3' => 'denied'
			);

			$this->sendJson($message);
		}
	}

}
