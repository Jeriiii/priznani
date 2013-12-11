<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class Pages_galleries extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('pages_galleries', $connection);
	}
	
	/**
	 * vrati vetev mezi danym uzlem a korenem
	 */
	
	public function getGalleries($id_page){
		return $this->query("
			SELECT * 
			FROM pages_galleries
			LEFT JOIN galleries
			ON pages_galleries.id_gallery=galleries.id
			WHERE pages_galleries.id_page=" . $id_page . "
		;");
	}
}
