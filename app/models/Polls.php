<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Polls extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('special_polls', $connection);
    }
}
