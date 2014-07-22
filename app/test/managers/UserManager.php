<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use \Behat\Behat\Exception\PendingException;
use Nette\Http\Session;
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
	 *
	 * @var string id of used session
	 */
	private $sessionId;

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
	 * @return string id of created session
	 */
	public function loginWithEmail($email) {
		$user = $this->userDao->findByEmail($email);
		if ($user) {
			$roles = array($user->role);
		} else {
			throw new PendingException('Uživatel s tímto emailem neexistuje. Opravdu je v testovací databázi?');
		}
		return $this->saveUserIntoSession($user, $roles);
	}

	/**
	 * Saves user into session (he is "logged in")
	 * @param \Nette\Database\Table\IRow $user user's row in database
	 * @param array $roles roles of the user
	 * @return string id of created session
	 */
	public function saveUserIntoSession($user, $roles) {
		$identity = new \Nette\Security\Identity($user->id, $roles, $user->toArray());
		$this->session->start();
		$userStorage = new UserStorage($this->session);
		$userStorage->setIdentity($identity);
		$userStorage->setAuthenticated(TRUE);
		$this->sessionId = $this->session->getId();
		$this->session->close();
	}

	/**
	 * Returns used session
	 * @return Session used session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * Returns used session id
	 * @return string id of used session
	 */
	public function getSessionId() {
		return $this->sessionId;
	}

	/**
	 * Clears sessions initialized with this manager
	 */
	public function clearSession() {
		if ($this->sessionId !== NULL) {
			session_id($this->sessionId);
			session_start();
			session_destroy();
			$this->sessionId = NULL;
		}
	}

}
