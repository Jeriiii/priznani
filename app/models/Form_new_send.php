<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Form_new_send extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('form_new_send', $connection);
    }
}
