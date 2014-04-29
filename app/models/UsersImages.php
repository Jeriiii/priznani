<?php
use Nette\Database\Table\Selection;



class UsersImages extends Selection
{

	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('user_images', $connection);
	}
	
	public function getAllUserFotos()
	{
		return $this;
	}
	
	public function findUserFoto(array $by)
	{
		return $this->where($by);
	}
 
	public function updateUserFoto(ActiveRow $user, array $values)
	{
		// todo validate values
		$user->update($values);
	}

	public function insertUserFoto(array $values)
	{
		// todo validate values
		return $this->insert($values)->id;
	}
	

}
