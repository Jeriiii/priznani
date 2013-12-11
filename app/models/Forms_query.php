<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Forms_query extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('forms_query', $connection);
    }
}
