<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Images extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('images', $connection);
    }
}
