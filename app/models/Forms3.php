<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Forms3 extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('forms3', $connection);
    }
}
