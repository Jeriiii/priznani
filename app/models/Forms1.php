<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Forms1 extends Confessions
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('confessions', $connection);
    }
	
}
