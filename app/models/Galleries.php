<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Galleries extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('galleries', $connection);
    }
}
