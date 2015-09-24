<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Notify;

use POS\Model\ActivitiesDao;
use POS\Model\ChatMessagesDao;
use POS\Model\UserDao;
use POS\Model\ConfessionDao;
use POS\Model\UserPropertyDao;
use NetteExt\Helper\GetImgPathHelper;
use POS\Model\UserImageDao;

/**
 * Připravuje emaily oznámení pro určité použití cronem
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CronNotifies extends CronEmails {
	/* maximální počet mužů, k nimž se může zobrazit profilovka */

	const MEN_TO_SHOW = 4;

	/* maximální počet žen, k nimž se může zobrazit profilovka */
	const WOMEN_TO_SHOW = 4;

	/** @var \POS\Model\ActivitiesDao */
	public $activitiesDao;

	/** @var \POS\Model\ChatMessagesDao */
	public $chatMessagesDao;

	/** @var \POS\Model\ConfessionDao */
	public $confessionDao;

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\UserImageDao */
	public $userImageDao;

	/** @var \POS\Model\UserPropertyDao */
	public $userPropertyDao;

	/** @var \Nette\Http\Url */
	public $urlObject;

	/** @var \Nette\Database\Table\Selection Uživatelé, kteří by měli dostat informační email. */
	public $usersForNewsletters;

	/** @var string Odkaz na týdenní změnu odesílání info emailu */
	private $weeklyLink;

	public function __construct(ActivitiesDao $activitiesDao, ChatMessagesDao $chatMessagesDao, UserDao $userDao, ConfessionDao $confessionDao, UserPropertyDao $userPropertyDao, UserImageDao $userImageDao) {
		$this->activitiesDao = $activitiesDao;
		$this->chatMessagesDao = $chatMessagesDao;
		$this->usersForNewsletters = $userDao->getForNeswletters();
		$this->userDao = $userDao;
		$this->userPropertyDao = $userPropertyDao;
		$this->confessionDao = $confessionDao;
		$this->userImageDao = $userImageDao;
	}

	/**
	 * Nastaví odkaz pro tlačítko, přepínající na dodávání newsletterů pouze jednou za týden
	 * @param String $link odkaz
	 */
	public function setWeeklyLink($link) {
		$this->weeklyLink = $link;
	}

	/**
	 * Nastaví url pro generování cest k obrázkům
	 * @param \Nette\Http\Url $urlObject url z kontextu presenteru
	 */
	public function setUrl($urlObject) {
		$this->urlObject = $urlObject;
	}

	/**
	 * Vrátí objekt pro práci či odeslání oznámení uživatelům emailem.
	 * @return EmailNotifies Objekt pro práci s oznámeními uživatelům.
	 */
	public function createEmails() {
		$activities = $this->activitiesDao->getNotViewedNotSendNotify($this->usersForNewsletters);
		$messages = $this->chatMessagesDao->getNotReadedNotSendNotify($this->usersForNewsletters);

		$emailNotifies = new EmailNotifies($this->weeklyLink);

		$emailNotifies->setConfessionText($this->getConfessionText());
		$this->setUsersPhotos($emailNotifies);
		/* upozornění na aktivity */
		foreach ($activities as $activity) {
			$emailNotifies->addActivity($activity->event_owner, $activity);
		}

		/* upozornění na zprávy */
		foreach ($messages as $message) {
			if (isset($message->id_recipient)) { //konverzace jsou null a tak se neposílají
				$emailNotifies->addMessage($message->recipient);
			}
		}

		return $emailNotifies;
	}

	/**
	 * Oznámí, že všechny emaily co mohli být do teď odeslány, opravdu odeslány jsou
	 */
	public function markEmailsLikeSended() {
		$this->activitiesDao->updateSendNotify($this->usersForNewsletters);
		$this->chatMessagesDao->updateSendNotify($this->usersForNewsletters);
	}

	/**
	 * Vrátí text (náhodného) přiznání k zobrazení.
	 * @return string text přiznání
	 */
	private function getConfessionText() {
		$confessions = $this->confessionDao->getBestConfessions(60)->fetchPairs(ConfessionDao::COLUMN_ID, ConfessionDao::COLUMN_NOTE);
		return $confessions[array_rand($confessions)];
	}

	/**
	 * Nastaví notifikačnímu emailu seznam fotek uživatelů, kteří se mají zobrazovat.
	 * @param EmailNotifies $emailNotifies
	 */
	private function setUsersPhotos($emailNotifies) {
		if (empty($this->urlObject)) {
			return;
		}
		$imagePathHelper = new GetImgPathHelper($this->urlObject);
		$users = $this->userDao->getAll(" DESC")->limit(60);
		$this->filterUsersToNotifies($emailNotifies, $users, $imagePathHelper);
	}

	/**
	 * Přidá uživatele mezi fotky do notifikačního výběru, pokud se do něj hodí
	 * @param EmailNotifies $emailNotifies notifikace, do kterých mají být přiřazeny fotky uživatelů
	 * @param Selection $users
	 * @param GetImgPathHelper $imagePathHelper
	 */
	private function filterUsersToNotifies($emailNotifies, $users, $imagePathHelper) {
		$menCount = 0;
		$womenCount = 0;
		foreach ($users as $user) {
			$userProperty = $this->userPropertyDao->find($user->offsetGet(UserDao::COLUMN_PROPERTY_ID));
			$userProfilePhoto = $this->userImageDao->find($user->profilFotoID);
			if (empty($userProfilePhoto->isOnFrontPage)) {
				continue;
			}
			if ($userProperty->type == UserDao::PROPERTY_MAN && $menCount < self::MEN_TO_SHOW) {
				$emailNotifies->addUserPhotoToShow($imagePathHelper->getImgMinPath($userProfilePhoto, GetImgPathHelper::TYPE_USER_GALLERY));
			}
			if ($userProperty->type == UserDao::PROPERTY_WOMAN && $womenCount < self::WOMEN_TO_SHOW) {
				$emailNotifies->addUserPhotoToShow($imagePathHelper->getImgMinPath($userProfilePhoto, GetImgPathHelper::TYPE_USER_GALLERY));
			}
			if ($menCount >= self::MEN_TO_SHOW && $womenCount >= self::WOMEN_TO_SHOW) {/* když už mám 4 od každého */
				break;
			}
		}
	}

}
