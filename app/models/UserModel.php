<?php

use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Identity;

/**
 * User model
 *
 * @author Jan Marek
 */
class UserModel
{

	/** @var \Nette\Database\Connection */
	private $database;

	public function __construct(Connection $db)
	{
		$this->database = $db;
	}

	public function find()
	{
		return $this->database;
	}
	
	public function findUsers()
	{
		return $this->database->table('users');
	}
	
	public function findUser(array $by)
	{
		return $this->database->table('users')->where($by)->fetch();
	}
	
	public function updateUser(ActiveRow $user, array $values)
	{
		// todo validate values
		$user->update($values);
	}
	
	public function findUserPartner(array $by){
		return $this->database->table('couple')->where($by)->fetch();
	}

	public function registerUser(array $values)
	{
		// todo validate values
		$this->database->table('users')->insert($values);
	}

	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($user['password']);
		return new \Nette\Security\Identity($user->id, NULL, $data);
		//return new \Nette\Security\Identity($user->id, NULL, $data);
	}

}
