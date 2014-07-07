<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use \Behat\Behat\Exception\PendingException;

/**
 * CLass working with users sessions
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class UserManager {

	/** @var Session */
	private $session;

	/**
	 * @var \POS\Model\UserDao
	 */
	private $userDao;

	/**
	 * Creates the session manager.
	 * @param \POS\Model\UserDao $userDao
	 */
	function __construct(\POS\Model\UserDao $userDao) {
		$this->userDao = $userDao;
	}

	public function loginWithEmail($email) {
		$user = $this->userDao->findByEmail($email);
		if ($user) {
			$roles = array($user->role);
		} else {
			throw new PendingException('Uživatel s tímto emailem neexistuje. Opravdu je v testovací databázi?');
		}
		$identity = new \Nette\Security\Identity($user->getPrimary(), $roles);
	}

}
