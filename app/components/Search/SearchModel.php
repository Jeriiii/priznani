<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchModel
 *
 * @author Mario
 */
class SearchModel extends Nette\Object {

	private $database;

	public function __construct(\Nette\Database\Table\Selection $database) {
		$this->database = $database;
	}

	/*
	 * vrati celeho uzivatele z databaze
	 */

	public function getUsersFromDB() {
		return $this->database->limit(8);
	}

	public function getAllUsersFromDB() {
		return $this->database;
	}

}
