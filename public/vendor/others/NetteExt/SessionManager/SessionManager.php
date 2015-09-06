<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 31.5.2015
 */

namespace NetteExt\Session;

use POS\UserPreferences\StreamUserPreferences;
use POS\UserPreferences\SearchUserPreferences;
use POS\Model\UserDao;
use POS\Model\StreamDao;
use POS\Model\UserCategoryDao;
use POS\Model\PaymentDao;
use Nette\Http\Session;
use Nette\Database\Table\ActiveRow;
use Nette\ArrayHash;
use POS\Model\UserImageDao;
use POS\Model\UserGalleryDao;

/**
 * Obstarává správu hodnot v session
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class SessionManager {

	/** @var Nette\Http\Session Aktuální session */
	private $session;

	/** @var \Nette\Database\Table\ActiveRow | ArrayHash Přihlášený uživatel. */
	private $loggedUser;

	public function __construct(Session $session, $loggedUser) {
		$this->session = $session;
		$this->loggedUser = $loggedUser;
	}

	/**
	 * Vyčistí v session hodnotz ve streamu a hodnoty ve vyhledávání.
	 */
	public function cleanAllPreferences(UserDao $userDao, StreamDao $streamDao, UserCategoryDao $userCategoryDao) {
		$this->cleanSearchSession($userDao, $userCategoryDao);
		$this->cleanStreamPreferences($userDao, $streamDao, $userCategoryDao);
	}

	/**
	 * Vyčistí příspěvky stremu uložené v session.
	 */
	public function cleanStreamPreferences(UserDao $userDao, StreamDao $streamDao, UserCategoryDao $userCategoryDao) {
		/* vyčistí stream */
		$streamUserPref = new StreamUserPreferences($this->loggedUser, $userDao, $streamDao, $userCategoryDao, $this->session);
		$streamUserPref->calculate();
	}

	/**
	 * Vyčistí hodnoty pro vyhledávání uložené v session.
	 */
	public function cleanSearchSession(UserDao $userDao, UserCategoryDao $userCategoryDao) {
		$searchUserPref = new SearchUserPreferences($this->loggedUser, $userDao, $userCategoryDao, $this->session);
		$searchUserPref->calculate();
	}

	/**
	 * Přepočítá osobní data přihlášeného uživatele. Osobními daty se myslí
	 * jméno, profilovka, data z páru a pod.
	 */
	public function calculateLoggedUser(UserDao $userDao, PaymentDao $paymentDao, UserImageDao $userImageDao, UserGalleryDao $userGalleryDao) {
		UserSession::calculateLoggedUser($userDao, $this->loggedUser, $this->session, $paymentDao, $userImageDao, $userGalleryDao);
	}

	/**
	 * Vrátí přihlášeného uživatele jako active row.
	 * @param UserDao $userDao
	 * @return ActiveRow
	 */
	private function getActiveRowUser(UserDao $userDao) {
		if ($this->loggedUser instanceof ActiveRow) {
			return $this->loggedUser;
		}

		return $userDao->find($this->loggedUser->id);
	}

}
