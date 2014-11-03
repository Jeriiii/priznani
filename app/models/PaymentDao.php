<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\DateTime;
use Nette\Database\Table\ActiveRow;
use Nette\Http\SessionSection;

/**
 * PAYMENT DAO PaymentDao
 * slouží k práci s tabulkou payments - platby VIP
 *
 * @author Daniel Holubář
 */
class PaymentDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "payments";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_USER_ID = "userID";
	const COLUMN_CREATE = "create";
	const COLUMN_TYPE = "type";
	const COLUMN_FROM = "from";
	const COLUMN_TO = "to";

	/* typ platby */
	const TYPE_BANK_ACCOUNT = 1;

	/* druh účtu */
	const ACCOUNT_PREMIUM = 1;
	const ACCOUNT_EXCLUSIVE = 2;

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí všechny platby pro zobrazení přehledu v administraci
	 * @return Nette\Database\Table\Selection
	 */
	public function getPaymentsReview() {
		$sel = $this->createSelection(self::TABLE_NAME);
		$sel->select('payments.*,userID.user_name,userID.email');
		$sel->order(self::COLUMN_FROM);
		return $sel;
	}

	/**
	 * Vrátí boolean, jestli je uživatel aktuálně platící
	 * @param int $userID id uživatele
	 * @return bool TRUE - platící / FALSE - neplatící
	 */
	public function isUserPaying($userID) {
		$sel = $this->getUserPayments($userID);
		$row = $sel->fetch();
		return $this->exist($row);
	}

	/**
	 * Vrátí boolean, jestli je uživatel aktuálně platící a PREMIUM
	 * @param int $userID id uživatele
	 * @return bool TRUE - platící / FALSE - neplatící
	 */
	public function isUserPremium($userID) {
		$sel = $this->getUserPremium($userID);
		$row = $sel->fetch();
		return $this->exist($row);
	}

	/**
	 * Vrátí boolean, jestli je uživatel aktuálně platící a EXCLUSIVE
	 * @param int $userID id uživatele
	 * @return bool TRUE - platící / FALSE - neplatící
	 */
	public function isUserExclusive($userID) {
		$sel = $this->getUserExclusive($userID);
		$row = $sel->fetch();
		return $this->exist($row);
	}

	/**
	 * Vrátí platícího uživatele pokud platba existuje a je aktuální a je PREMIUM
	 * @param int $userID id uživatele
	 * @return Nette\Database\Table\Selection
	 */
	public function getUserPremium($userID) {
		$sel = $this->getUserPayments($userID);
		$sel->where(self::COLUMN_TYPE, self::ACCOUNT_PREMIUM);
		return $sel;
	}

	/**
	 * Vrátí platícího uživatele pokud platba existuje a je aktuální a je eXclusive
	 * @param int $userID id uživatele
	 * @return Nette\Database\Table\Selection
	 */
	public function getUserExclusive($userID) {
		$sel = $this->getUserPayments($userID);
		$sel->where(self::COLUMN_TYPE, self::ACCOUNT_EXCLUSIVE);
		return $sel;
	}

	/**
	 * Vrátí sekci pro payment.
	 * @return SessionSection
	 */
	public function getPaymentSection() {
		$sectionName = self::SECTION_DB_PREFFIX . "-" . "payment";
		$section = $this->session->getSection($sectionName);
		return $section;
	}

}
