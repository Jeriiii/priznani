<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;


class News_galleries extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('news_galleries', $connection);
	}
	
	/**
	 * vrati vetev mezi danym uzlem a korenem
	 */
	
	public function getGalleries($id_new){
		return $this->query("
			SELECT * 
			FROM news_galleries
			LEFT JOIN galleries
			ON news_galleries.id_gallery=galleries.id
			WHERE news_galleries.id_new=" . $id_new . "
		;");
	}
}
