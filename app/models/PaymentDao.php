<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * PAYMENT DAO PaymentDao
 * slouží k práci s tabulkou payments - platby VIP
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class PaymentDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "payments";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_CREATE = "create";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getPaymentsData() {
		$sel = $this->createSelection(self::TABLE_NAME);
		$data = $sel->select('payments.*,userID.user_name,userID.email');
		return $data;
	}

	/**
	 * TODO
	 * Vrátí boolean, jestli je uživatel aktuálně platící
	 * @param int $idUser id uživatele
	 * @return bool TRUE - platící / FALSE - neplatící
	 */
	public function isUserPaying($idUser) {
		$sel = $this->getTable();
		//TODO
		return TRUE;
	}

}
