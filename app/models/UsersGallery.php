<?php
use Nette\Database\Table\Selection;



class UsersGallery extends Selection
{

	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('user_galleries', $connection);
	}
 

}
