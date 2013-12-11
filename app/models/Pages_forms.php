<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Pages_forms extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('pages_forms', $connection);
	}
	
	/**
	 * vrati vetev mezi danym uzlem a korenem
	 */
	
	public function getForm($id_page){
		return $this->query("
			SELECT * 
			FROM pages_forms
			LEFT JOIN forms
			ON pages_forms.id_form=forms.id
			WHERE pages_forms.id_page=" . $id_page . "
		;");
	}
}
