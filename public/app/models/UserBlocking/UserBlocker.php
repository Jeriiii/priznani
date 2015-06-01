<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 19.5.2015
 */

namespace UserBlock;

use NetteExt\DaoBox;
use NetteExt\Session\SessionManager;

/**
 * Stará se o zablokování / odblokování uživatele
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserBlocker {

	/** @var \POS\Model\UserBlockedDao */
	public $userBlockedDao;

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	public function __construct(DaoBox $daoBox) {
		$this->userBlockedDao = $daoBox->userBlockedDao;
		$this->userDao = $daoBox->userDao;
		$this->streamDao = $daoBox->streamDao;
		$this->userCategoryDao = $daoBox->userCategoryDao;
	}

	/**
	 * Zablokuje uživatele.
	 * @param int $blockUserID Id uživatele, co má být zablokován.
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $loggedUser Přihlášený uživatel.
	 * @param \Nette\Http\Session $session
	 */
	public function blockUser($blockUserID, $loggedUser, $session) {
		/* zablokuje uživatele */
		$this->userBlockedDao->addBlocking($loggedUser->id, $blockUserID);

		$this->cleanCache($loggedUser, $session);
	}

	/**
	 * Odblokuje uživatele.
	 * @param int $blockUserID Id uživatele, co má být odblokován.
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $loggedUser Přihlášený uživatel.
	 * @param \Nette\Http\Session $session
	 */
	public function unblockUser($blockUserID, $loggedUser, $session) {
		/* zablokuje uživatele */
		$this->userBlockedDao->removeBloking($loggedUser->id, $blockUserID);

		$this->cleanCache($loggedUser, $session);
	}

	/**
	 * Vyčistí cache tak, aby se projevili změny u zablokovaného / odblokovaného uživatele.
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $loggedUser Přihlášený uživatel.
	 * @param \Nette\Http\Session $session
	 */
	private function cleanCache($loggedUser, $session) {
		$sm = new SessionManager($session, $loggedUser);
		$sm->cleanAllPreferences($this->userDao, $this->streamDao, $this->userCategoryDao);
	}

}
