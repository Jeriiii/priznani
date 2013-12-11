<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Advices extends Confessions
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('advices', $connection);
    }
	
}
