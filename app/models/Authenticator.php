<?php

use Nette\Security as NS;
use POS\Model\UserDao;

/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @param POS\Model\UserDao $userDao
	 */
	public function __construct(UserDao $userDao) {
		$this->userDao = $userDao;
	}

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {
		list($email, $password) = $credentials;
		$user = $this->userDao->findByEmail($email);

		if (!$user) {
			throw new NS\AuthenticationException("Neplatné uživatelské jméno nebo heslo.", self::IDENTITY_NOT_FOUND);
		}

		if ($user->password !== self::calculateHash($password)) {
			throw new NS\AuthenticationException("Neplatné uživatelské jméno nebo heslo.", self::NOT_APPROVED);
		}

		if ($user->role == "unconfirmed_user") {
			throw new NS\AuthenticationException("Nejdřív musíte potvrdit e-mail který byl zaslán při registraci", self::INVALID_CREDENTIAL);
		}

		$arr = $user->toArray();
		return new NS\Identity($user->id, $user->role, $arr);
	}

	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password) {
		return hash('sha512', $password);
	}

	public function setPassword($id, $password) {
		$this->users->where(array('id' => $id))
			->update(array('password' => $this->calculateHash($password)));
	}

}
