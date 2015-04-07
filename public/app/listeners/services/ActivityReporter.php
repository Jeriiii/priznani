<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners\Services;

use POS\Model\UserDao;
use Nette\Http\Session;
use Nette\DateTime;
use DateInterval;
use Nette\Http\SessionSection;

/**
 * ActivityReporter slouží jako služba pro DI container, která může informovat listenery o aktivitě uživatele.
 * Přidává i informace o četnosti přihlašování.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ActivityReporter extends \Nette\Object {

	/**
	 * Pro listenery při jakékoli aktivitě přihlášeného uživatele.
	 * Jako parametr dostane funkce id přihlášeného uživatele.
	 */
	public $onUserActivity = array();

	/**
	 * Pro listenery při první aktivitě přihlášeného uživatele tento den.
	 * Jako parametr dostane funkce id přihlášeného uživatele a jako druhý parametr, kolikátý den po sobě se přihlásil (1 pokud dnes poprvé).
	 */
	public $onUserFirstTodayActivity = array();

	/**
	 * @var SessionSection
	 */
	public $section;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * Název sekce v sešně
	 * Obsahuje proměnné:
	 * lastActivity - den poslední aktivity
	 * oldestStreakActivity - den první aktivity, která byla dosud následována každý den
	 */
	const SECTION_NAME = 'SignRewardListenerSection';

	public function __construct(Session $session, UserDao $userDao) {
		$this->section = $session->getSection(self::SECTION_NAME);
		$this->userDao = $userDao;

		$testing = (isset($_SERVER['TESTING']) && $_SERVER['TESTING']) ||
			(isset($_SERVER['HTTP_X_TESTING']) && $_SERVER['HTTP_X_TESTING']);
		if (!$testing) {
			$this->section->setExpiration('30 days');
		}
	}

	/**
	 * Zavolá se při aktivitě přihlášeného uživatele. Mělo by se volat pokaždé z presenteru,
	 * nad kterým chceme sledovat aktivitu. V případě POS jde o BasePresenter.
	 * @param type $user uživatel z presenteru
	 */
	public function handleUsersActivity($user) {
		$section = $this->section; //načtení sešny
		$this->onUserActivity($user->getId());
		$this->loadSessionData($user->id, $section); //po tomhle kroku mám v sešně určitě data
		if (!$this->isToday($section->lastActivity)) {//pokud je poslední aktivita dneska, nedělám nic
			if ($this->isYesterday($section->lastActivity)) {//pokud je poslední aktivita včera (tj. přihlásil jsem se i včera)
				$this->updateActivity($user->getId(), $section); //aktualizuji jen aktivitu
			} else {
				$this->updateActivityAndResetStreak($user->getId(), $section); //aktualizuji aktivitu a nastavím dnešek jako první den sériové aktivity
			}
			$this->fireFirstTodayActivityEvent($user->getId(), $section->oldestStreakActivity);
		}
	}

	/**
	 * Načte související data do sešny, pokud tam nejsou. Každopádně v sešně budou po skončení této metody data.
	 * @param int $userID id přihlášeného uživatele
	 * @param \Nette\Http\SessionSection $section sekce v sešně
	 */
	public function loadSessionData($userID, $section) {
		if (!$section->lastActivity || !$section->oldestStreakActivity) {
			$user = $this->userDao->find($userID);
		}
		if (!$section->lastActivity) {//sešna je nová - neobsahuje čas poslední aktivity
			$this->loadLastActivity($user, $section);
		}
		if (!$section->oldestStreakActivity) {//sešna je nová - neobsahuje čas poslední aktivity
			$this->loadOldestStreakActivity($user, $section);
		}
	}

	/**
	 * Načte do sešny čas poslední aktivity. Pokud v databázi vůbec není, nastaví se na předvčerejšek.
	 * @param \Nette\Database\Table\Selection $user
	 * @param \Nette\Http\SessionSection $section
	 */
	public function loadLastActivity($user, $section) {
		if (!$user->offsetGet(UserDao::COLUMN_LAST_SIGNED_DAY)) {//pokud je v db stále NULL
			$now = new DateTime();
			$section->lastActivity = $now->modify('-2 days'); //předevčírem
			$this->userDao->update($user->id, array(
				UserDao::COLUMN_LAST_SIGNED_DAY => $section->lastActivity
			));
		} else {
			$section->lastActivity = $user->offsetGet(UserDao::COLUMN_LAST_SIGNED_DAY);
		}
	}

	/**
	 * Načte do sešny čas první aktivity, která byla dosud obnovena každý den
	 * @param \Nette\Database\Table\Selection $user
	 * @param \Nette\Http\SessionSection $section
	 */
	public function loadOldestStreakActivity($user, $section) {
		if (!$user->offsetGet(UserDao::COLUMN_FIRST_SIGNED_DAY_STREAK)) {//pokud je v db stále NULL
			$section->oldestStreakActivity = new DateTime();
			$this->userDao->update($user->id, array(
				UserDao::COLUMN_FIRST_SIGNED_DAY_STREAK => $section->oldestStreakActivity
			));
		} else {
			$section->oldestStreakActivity = $user->offsetGet(UserDao::COLUMN_FIRST_SIGNED_DAY_STREAK);
		}
	}

	/**
	 * Zajistí "vystřelení" události oznamující, že se uživatel poprvé tento den přihlásil
	 * @param int $userID id uživatele
	 * @param int $firstStreakDay kolikátý den po sobě se přihlásil
	 */
	public function fireFirstTodayActivityEvent($userID, $firstStreakDay) {
		$now = new DateTime();
		$between = $firstStreakDay->diff($now);
		$dayOfStreak = $between->days + 1; //kolikátý den po sobě
		$this->onUserFirstTodayActivity($userID, $dayOfStreak);
	}

	/**
	 * Rozhodne, zda je daný čas dnes
	 * @param DateTime $lastActivity Čas poslední aktivity uživatele.
	 * @return bool
	 */
	private function isToday($lastActivity) {
		$now = new DateTime();
		$diff = $now->diff($lastActivity);

		return $diff->days == 0;
	}

	/**
	 * Rozhodne, zda je daný čas včera
	 * @param DateTime $dateTime daný čas
	 * @return bool
	 */
	private function isYesterday($dateTime) {
		$now = new DateTime();
		$diff = $dateTime->diff($now);
		return $diff->days == 1;
	}

	/**
	 * Aktualizuje čas poslední aktivity v databázi a sešně
	 * @param int $userID id přihlášeného uživatele
	 * @param \Nette\Http\SessionSection $section sekce v sešně
	 */
	private function updateActivity($userID, $section) {
		$now = new DateTime(); //updatnu aktivitu v databázi
		$this->userDao->update($userID, array(
			UserDao::COLUMN_LAST_SIGNED_DAY => $now
		));
		$section->lastActivity = $now;
	}

	/**
	 * Aktualizuje čas poslední aktivity v databázi a sešně a zároveň nastaví
	 * první den nepřetržitého přihlášení na dnešek, rovněž v sešně i databázi
	 * @param int $userID id přihlášeného uživatele
	 * @param \Nette\Http\SessionSection $section sekce v sešně
	 */
	private function updateActivityAndResetStreak($userID, $section) {
		$now = new DateTime(); //updatnu v databázi aktivitu i první den nepřerušeného přihlášení
		$this->userDao->update($userID, array(
			UserDao::COLUMN_LAST_SIGNED_DAY => $now,
			UserDao::COLUMN_FIRST_SIGNED_DAY_STREAK => $now
		));
		$section->lastActivity = $now;
		$section->oldestStreakActivity = $now;
	}

}
