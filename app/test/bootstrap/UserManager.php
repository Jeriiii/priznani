<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

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
	 * @param Session $session
	 * @param UserDao $userDao
	 */
	function __construct(\POS\Model\UserDao $userDao) {
		$this->session = $session;
		$this->userDao = $userDao;
	}

	public function login($username) {
		//$user = $this->userDao->findByEmail($username);
		//$roles = array($user->role);
		//$identity = new \Nette\Security\Identity($user->getPrimary(), $roles);
		//dump($username);
	}

}
