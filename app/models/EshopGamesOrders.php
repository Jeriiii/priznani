<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;

class EshopGames extends UsersBase
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('eshop_games_orders', $connection);
    }
	
}
