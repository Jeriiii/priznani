<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\SqlLiteral;
use Authorizator;

/**
 * Uživatelé UsersDao
 * slouží k práci s uživateli
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserDao extends UsersBaseDao {

	const TABLE_NAME = "users";

	/* sloupce */
	const COLUMN_EMAIL = "email";
	const COLUMN_ROLE = "role";
	const COLUMN_ADMIN_SCORE = "admin_score";
	const COLUMN_CONFIRMED = "confirmed";
	const COLUMN_PASSWORD = "password";
	const COLUMN_USER_NAME = "user_name";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Najde uživatele podle jeho emailu
	 * @param String $email Email uživatele
	 * @return bool|Database\Table\IRow
	 */
	public function findByEmail($email) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_EMAIL, $email);
		return $sel->fetch();
	}

	/**
	 * Vrátí seznam adminů s jejich score (bez superadmina Jerry)
	 * @return Nette\Database\Table\Selection
	 */
	public function getAdminScore() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ROLE . " = ? OR " . self::COLUMN_ROLE . " = ?", Authorizator::ROLE_ADMIN, Authorizator::ROLE_SUPERADMIN);
		$sel->where("NOT " . self::COLUMN_USER_NAME, "Jerry");
		$sel->order(self::COLUMN_ADMIN_SCORE . " DESC");
	}

	/**
	 * Najde uživatele podle jeho nicku
	 * @param string $userName Nick uživatele.
	 * @return bool|Database\Table\IRow
	 */
	public function findByUserName($userName) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_USER_NAME, $userName);
		return $sel->fetch();
	}

	/**
	 * Vrátí uživatele podle potvrzovacího kódu (který mu byl zaslán na jeho email).
	 * @param string $confirmCode
	 */
	public function findByConfirm($confirmCode) {
		$sel = $this->getTable();
		$sel->where(COLUMN_CONFIRMED, $confirmCode);
		return $sel->fetch();
	}

	/**
	 * Vrátí nepotvrzené uživatele
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUnconfirmed() {
		return $this->getUsersByRole(Authorizator::ROLE_UNCONFIRMED_USER);
	}

	/**
	 * Vrátí všechny uživatele v roli user
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUsers() {
		return $this->getUsersByRole(Authorizator::ROLE_USER);
	}

	/**
	 * Vrátí všechny uživatele v roli user
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleAdmin() {
		return $this->getUsersByRole(Authorizator::ROLE_ADMIN);
	}

	/**
	 * Vrátí všechny uživatele v roli superadmin
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleSuperadmin() {
		return $this->getUsersByRole(Authorizator::ROLE_SUPERADMIN);
	}

	/**
	 * Vrátí uživatele podle role
	 * @param String Role uživatele
	 * @return Nette\Database\Table\Selection
	 */
	private function getUsersByRole($role) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ROLE, $role);
		return $sel;
	}

	/**
	 * Vrácí všechna data o uživateli, nikoliv o partnerovi
	 * @param int $userID ID uživatele
	 * @return bool|Database\Table\IRow
	 */
	public function getUserData($userID) {
		$select = $this->getTable->find($userID);
		$select->find();
		$user = $select->fetch();

		$baseUserData = array(
			'Jméno' => $user->user_name,
			'První věta' => $user->first_sentence,
			/* 'Naposledy online' => $user->last_active, */
			'Druh uživatele' => Users::getTranslateUserProperty($user->user_property),
			/* 'Vytvoření profilu' => $user->created, */
			/* 'Email' => $user->email, */
			'O mně' => $user->about_me,
		);
		$baseData = $this->getBaseData($user);
		$other = $this->getOtherData($user);
		$sex = $this->getSex($user);

		return $baseUserData + $baseData + $other + $sex;
	}

	/**
	 * Zvýší adminovi score
	 * @param int $adminID ID administrátora, co přiznání schválil,
	 * @param int $value O kolik se má zvýšit.
	 */
	public function increaseAdminScore($adminID, $value) {
		$sel = $this->getTable();
		$sel->wherePrimary($adminID);
		$sel->update(array(
			self::COLUMN_ADMIN_SCORE => new SqlLiteral(self::COLUMN_ADMIN_SCORE . ' + ' . $value)
		));
	}

	/*	 * ************************** UPDATE *************************** */

	public function setUserRoleByConfirm($confirmCode) {
		$user = $this->findByConfirm($confirmCode);
		$user->update(array(
			self::COLUMN_ROLE => \Authorizator::ROLE_USER
		));
	}

	/**
	 * Nastaví roli na super admin.
	 * @param int $id ID uživatele.
	 */
	public function setSuperAdminRole($id) {
		$this->updateRole($id, \Authorizator::ROLE_SUPERADMIN);
	}

	/**
	 * Nastaví roli na admin.
	 * @param int $id ID uživatele.
	 */
	public function setAdminRole($id) {
		$this->updateRole($id, \Authorizator::ROLE_ADMIN);
	}

	/**
	 * Nastaví roli na user.
	 * @param int $id ID uživatele.
	 */
	public function setUserRole($id) {
		$this->updateRole($id, \Authorizator::ROLE_USER);
	}

	/**
	 * Nastaví roli na nepotvrzeného uživatele.
	 * @param int $id ID uživatele.
	 */
	public function setUnconfirmedUserRole($id) {
		$this->updateRole($id, \Authorizator::ROLE_UNCONFIRMED_USER);
	}

	public function setPassword($userID, $password) {
		$sel = $this->getTable();
		$sel->wherePrimary($userID);
		$sel->update(array(
			self::COLUMN_PASSWORD, $password
		));
	}

	/**
	 * Změní roli uživateli. Používejte setRole metody.
	 * @param int $id ID uživatele
	 * @param string $role Nová role uživatele
	 */
	private function updateRole($id, $role) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_ROLE => $role
		));
	}

}
