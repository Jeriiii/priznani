<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection,
	Nette\Database\SqlLiteral;


/*
 * předek všech přiznání
 */

class Confessions extends Selection
{
	
	/**
	 * vrátí vydaná přiznání
	 */
	
	public function getPublishedConfession($order = NULL, $duplicate = FALSE)
	{
		if(empty($order)) $order = "sort_date";
		$database = $this;
		if(!$duplicate) $database->where('mark != ?', 4);
		
		return $database
					->where('release_date <= ?', new DateTime())
					->order($order . " DESC");
	}
	
	/**
	 * vrátí přiznání podle textu
	 */
	
	public function getConnectionsLikeText($text) {
		$text = trim($text);
		return $this
					->where("note LIKE ?", "%" . $text . "%");
	}
	
	/**
	 * vrátí první přiznání podle textu
	 */
	
	public function getConnectionLikeText($text) {
		return $this->getConnectionsLikeText($text)->fetch();
	}

	/**
	 * zjistí, zda dané přiznání již existuje
	 */
	
	public function existConnectionLikeText($text) {
		$exist =  $this->getConnectionLikeText($text);
		if($exist)
			return TRUE;
		else
			return FALSE;
	}
	
	/**
	 * vrátí poslední naplánované přiznání
	 */
	
	public function getLastScheduledConfession()
	{
		return $this
					->where("NOT release_date", NULL)
					->order("release_date DESC")
					->fetch();
	}
	
	/**
	 * vrati priznani, ktere ma brzi vyjit
	 */
	
	public function getConfessionRelease()
	{
		return $this
					->where('release_date > ?', new DateTime())
					->order("release_date ASC")
					->fetch();
	}
	
	/* zvysi like u priznani o jedna */
	
	public function incLike($id_confession) 
	{
		$this
			->find($id_confession)
			->update(array(
				'sort_date' => new Nette\DateTime,
				'fblike' => new SqlLiteral('fblike + 1')
			));

	}
	
		/* snizi like u priznani o jedna */
	
	public function decLike($id_confession) 
	{
		$this
			->find($id_confession)
			->update(array(
				'sort_date' => new Nette\DateTime,
				'fblike' => new SqlLiteral('fblike - 1')
			));
	}
	
	/* zvysi like u priznani o jedna */
	
	public function incComment($id_confession) 
	{
		$this
			->find($id_confession)
			->update(array(
				'sort_date' => new Nette\DateTime,
				'comment' => new SqlLiteral('comment + 1')
			));
				
	}
	
		/* snizi like u priznani o jedna */
	
	public function decComment($id_confession) 
	{
		$this
			->find($id_confession)
			->update(array(
				'sort_date' => new Nette\DateTime,
				'comment' => new SqlLiteral('comment - 1')
			));
				
	}
	
}
