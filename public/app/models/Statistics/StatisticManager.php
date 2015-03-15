<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

/**
 * Stará se o správu statistik. Je možn= ho požádat o libovolnou statistiku. Počítání
 * probíhá lazy až v momentě dotazu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POS\Statistics;

use Nette\Database\Table\Selection;
use POS\Model\UserDao;
use POS\Model\StreamDao;
use POS\Model\UserImageDao;
use Nette\DateTime;

class StatisticManager {

	/** @var UserDao */
	private $userDao;

	/** @var StreamDao */
	public $streamDao;

	/** @var UserImageDao */
	public $userImageDao;

	public function setUserDao($userDao) {
		$this->userDao = $userDao;
	}

	public function setStreamDao($streamDao) {
		$this->streamDao = $streamDao;
	}

	public function setUserImageDao($userImageDao) {
		$this->userImageDao = $userImageDao;
	}

	/**
	 * Vrací objekt pro práci s registracemi a statistikami.
	 * @return RegistrationStatistics Spočítá statistiky registrovaných uživatelů
	 */
	public function getRegUsers() {
		$regStat = new RegistrationStatistics($this->userDao);
		return $regStat;
	}

	/**
	 * Vrací objekt pro práci se statusy a statistikami.
	 * @return StreamStatusStatistics Spočítá statistiky registrovaných uživatelů
	 */
	public function getStreamStatus() {
		$statStat = new StreamStatusStatistics($this->streamDao);
		return $statStat;
	}

	/**
	 * Vrací objekt pro práci s nahráním obrázků a jejich statisitkami.
	 * @return StreamUserGalleriesStatistics Spočítá statistiky se změnami galerií
	 */
	public function getUserImagesGallery() {
		$imgsStat = new UserImagesStatistics($this->userImageDao);
		return $imgsStat;
	}

	/**
	 * Vrací objekt pro práci s přiznáními a statistikami.
	 * @return StreamConfessionsStatistics Spočítá statistiky registrovaných uživatelů
	 */
	public function getStreamConfessions() {
		$confStat = new StreamConfessionsStatistics($this->streamDao);
		return $confStat;
	}

	/**
	 * Vrací objekt pro práci s počtem jednotlivých skupin uživatelů.
	 * @return PeopleBySexStatistics Spočítá statistiky skupin uživatelů
	 */
	public function getPeopleBySex() {
		$sexStat = new PeopleBySexStatistics($this->userDao);
		return $sexStat;
	}

	/**
	 * Vrací objekt pro práci s počtem jednotlivých skupin uživatelů.
	 * @return PeopleBySexStatistics Spočítá statistiky skupin uživatelů
	 */
	public function getPeopleByAge() {
		$sexStat = new PeopleByAgeStatistics($this->userDao);
		return $sexStat;
	}

}
