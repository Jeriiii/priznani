<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

class Couple extends POS\Model\UsersBaseDao {

	public function __construct(\Nette\Database\Connection $connection) {
		parent::__construct('couple', $connection);
	}

	/*
	 * vrácí všechna data o partnerovi bez dat o uživateli
	 */

	public function getPartnerData($id) {
		$user = $this->find($id)->fetch();
		$baseData = $this->getBaseData($user);
		$sex = $this->getSex($user);

		return $baseData + $sex;
	}

}
