<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;

class ActivityStream extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('stream_items', $connection);
    }
	
}
