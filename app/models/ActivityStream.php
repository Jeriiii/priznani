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
	
	public function addNewGallery($userGalleryID, $userID) {
		$this->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"create" => new DateTime(),
		));
	}
	
	public function aliveGallery($userGalleryID) {
		$this->where("userGalleryID", $userGalleryID)->delete();
		$this->insert(array(
			"userGalleryID" => $userGalleryID,
			"userID" => $userID,
			"create" => new DateTime(),
		));
	}
}
