<?php
use Nette\Database\Table\Selection;



class UsersGalleries extends Selection
{

	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('user_galleries', $connection);
	}
 

}
