<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use \Behat\Behat\Exception\PendingException;
use \Nette\Http\Session;
use Nette\Http\UserStorage;

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
	function __construct(Session $session, \POS\Model\UserDao $userDao) {
		$this->session = $session;
		$this->userDao = $userDao;
	}

	/**
	 * Simulates login of user. Finds him in testing database and
	 * adds his roles.
	 * @param String $email indentification of user - email
	 * @throws PendingException throwed if user is not in testing database
	 */
	public function loginWithEmail($email) {
		$user = $this->userDao->findByEmail($email);
		if ($user) {
			$roles = array($user->role);
		} else {
			throw new PendingException('Uživatel s tímto emailem neexistuje. Opravdu je v testovací databázi?');
		}
		$this->saveUserIntoSession($user, $roles);
	}

	/**
	 * Saves user into session (he is "logged in")
	 * @param \Nette\Database\Table\IRow $user
	 * @param array $roles
	 */
	public function saveUserIntoSession($user, $roles) {
		$this->session->start();
		$identity = new \Nette\Security\Identity($user->getPrimary(), $roles);
		$userStorage = new UserStorage($this->session);
		$userStorage->setIdentity($identity);
		$userStorage->setAuthenticated(TRUE);
		$this->session->close();
	}

	/**
	 * Returns used session
	 * @return Session used session
	 */
	public function getSession() {
		return $this->session;
	}

}
