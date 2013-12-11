<?php

use Nette\Security as NS;


/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	/** @var Nette\Database\Table\Selection */
	private $users;



	public function __construct(Nette\Database\Table\Selection $users)
	{
		$this->users = $users;
	}



	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;
		$row = $this->users->where('email', $email)->fetch();

		if (!$row) {
			throw new NS\AuthenticationException("Neplatné uživatelské jméno nebo heslo.", self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== self::calculateHash($password)) {
			throw new NS\AuthenticationException("Neplatné uživatelské jméno nebo heslo.", self::NOT_APPROVED);
		}
		
		if ($row->role == "unconfirmed_user") {
			throw new NS\AuthenticationException("Nejdřív musíte potvrdit e-mail který byl zaslán při registraci", self::INVALID_CREDENTIAL);
		}
		
//		if ($row->role == "user") {
//			throw new NS\AuthenticationException("Na vstup do této sekce nemáte oprávnění.", self::INVALID_CREDENTIAL);
//		}
		
//		return $this->userModel->createIdentity($row);

		unset($row->password);
		return new NS\Identity($row->id, $row->role, $row->toArray());
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password)
	{
		return hash('sha512', $password);
	}
	
	public function setPassword($id, $password)
	{
    	$this->users->where(array('id' => $id))
    	    ->update(array('password' => $this->calculateHash($password)));
	}

}
