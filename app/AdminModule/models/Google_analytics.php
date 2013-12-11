<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Google_analytics extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('google_analytics', $connection);
    }
}
