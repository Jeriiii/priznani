<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection,
	Nette\DateTime;

class ActivityStream extends Selection
{
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct('stream_items', $connection);
    }
     
         public function addNewConfession($confessionID, $userID) {
		$this->insert(array(
			"confessionID" => $confessionID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}
        
        public function addNewAdvice($adviceID, $userID) {
		$this->insert(array(
			"adviceID" => $adviceID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}
    
	public function addNewGallery($userGalleryID, $userID) {
		$this->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}
	
	public function aliveGallery($userGalleryID, $userID) {
		$this->where("userGalleryID", $userGalleryID)->delete();
		$this->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"type" => 1,
			"create" => new DateTime(),
		));
	}
}
