<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Authorizator_table extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('authorizator_table', $connection);
    }
}
