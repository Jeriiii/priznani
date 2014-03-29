<?php
use Nette\Database\Table\Selection;



class UsersFoto extends Selection
{

	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('users_fotos', $connection);
	}
 

}
