<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class News extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('news', $connection);
    }
}
