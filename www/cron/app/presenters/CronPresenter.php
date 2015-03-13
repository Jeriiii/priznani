<?php

namespace App\Presenters;

use Nette;
use App\Forms as Frm;
use Cron\MailReadJSON;

/**
 * Presenter na práci s crony
 */
class CronPresenter extends BasePresenter {

	/** @var \POS\Model\DatabaseDao @inject */
	public $databaseDao;

	public function actionDefault() {
		$mailer = new MailReadJSON($this->databaseDao);

		/* emaily s aktualitami */
		$mailer->sendEmails(self::urlBuilder('mail-to-json'));
		//$mailer->readUrl(self::urlBuilder('mail-is-sended')); //označí emaily jako odeslané

		/* emaily pro bývalé uživatele */
		$mailer->setEmailType(MailReadJSON::TYPE_EMAIL_OLD_USERS);
		$mailer->sendEmails(self::urlBuilder('mail-to-old-users-json'));
		//$mailer->readUrl(self::urlBuilder('mail-old-users-is-sended')); //označí emaily jako odeslané

		echo 'emaily byly odeslány';
		die();
	}

	/**
	 * Vytvoří url ze zadané action
	 * @param string $action Action presenteru.
	 */
	private static function urlBuilder($action = 'mail-to-json') {
		$domain = 'http://42750.w50.wedos.ws';
		$user = 'userName=mailuser&userPassword=a10b06001';
		$presenter = 'cron-email';
		$url = $domain . '/' . $presenter . '/' . $action . '?' . $user;
		return $url;
	}

	public function renderDefault() {

	}

}
