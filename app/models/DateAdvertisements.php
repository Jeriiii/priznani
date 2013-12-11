<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class DateAdvertisements extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('date_advertisements', $connection);
    }
}
