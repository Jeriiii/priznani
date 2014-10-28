<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use Nette\Database\SqlLiteral;
use Authorizator;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Uživatelé UsersDao
 * slouží k práci s uživateli
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserDao extends UserBaseDao {

	const TABLE_NAME = "users";
	const PROPERTIES_TABLE_NAME = "users_properties";

	/* sloupce */
	const COLUMN_COUPLE_ID = "coupleID";
	const COLUMN_PROPERTY_ID = "propertyID";
	const COLUMN_LAST_ACTIVE = "last_active";
	const COLUMN_CREATED = "created";
	const COLUMN_EMAIL = "email";
	const COLUMN_ROLE = "role";
	const COLUMN_ADMIN_SCORE = "admin_score";
	const COLUMN_CONFIRMED = "confirmed";
	const COLUMN_PASSWORD = "password";
	const COLUMN_VERIFIED = "verified";

	/**
	 * w - women
	 * m - man
	 * c - couple
	 * cw - coupleWomen
	 * cm - coupleMen
	 * g - group
	 */
	const COLUMN_USER_NAME = "user_name";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí další údaje o uživateli
	 * @param int $userID
	 * @return bool|Database\Table\IRow
	 */
	public function findProperties($userID) {
		$user = $this->find($userID);
		$sel = $this->createSelection(UserPropertyDao::TABLE_NAME);
		$sel->wherePrimary($user->propertyID);
		return $sel->fetch();
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
		$sel->where(self::COLUMN_CONFIRMED, $confirmCode);
		return $sel->fetch();
	}

	/**
	 * Vyhledá užigvatele podle zadaných kriterií
	 * @param array $data pole dat, podle kterých se provede hledání
	 * @return Nette\Database\Table\Selection
	 */
	public function findBySearchData($data) {
		$sel = $this->getTable();
		$timeOne = new \Nette\DateTime();
		$timeTwo = new \Nette\DateTime();

		if (empty($data)) {
			return $sel;
		}
		if (!empty($data['age_from'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".age <= ?", $timeOne->modify('-' . $data['age_from'] . 'years')->format('Y-12-31'));
		}
		if (!empty($data['age_to'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".age >= ?", $timeTwo->modify('-' . $data['age_to'] . 'years')->format('Y-1-1'));
		}
		if (!empty($data['sex'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".type", $data['sex']);
		}
		if (!empty($data['penis_length_from'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".penis_length >= ?", $data['penis_length_from']);
		}
		if (!empty($data['penis_length_to'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".penis_length <= ?", $data['penis_length_to']);
		}
		if (!empty($data['penis_width'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".penis_width", $data['penis_width']);
		}
		if (!empty($data['bra_size'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".bra_size", $data['bra_size']);
		}
		if (!empty($data['orientation'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".orientation", $data['orientation']);
		}
		if (!empty($data['shape'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".shape", $data['shape']);
		}
		if (!empty($data['hair_color'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".hair_colour", $data['hair_color']);
		}
		if (!empty($data['tallness_from'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".tallness >= ?", $data['tallness_from']);
		}
		if (!empty($data['tallness_to'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".tallness <= ?", $data['tallness_to']);
		}
		if (!empty($data['drink'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".drink", $data['drink']);
		}
		if (!empty($data['smoke'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".smoke", $data['smoke']);
		}
		if (!empty($data['men'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_men", $data['men']);
		}
		if (!empty($data['women'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_women", $data['women']);
		}
		if (!empty($data['couple'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_couple", $data['couple']);
		}
		if (!empty($data['men_couple'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_couple_men", $data['men_couple']);
		}
		if (!empty($data['women_couple'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_couple_women", $data['women_couple']);
		}
		if (!empty($data['more'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".want_to_meet_group", $data['more']);
		}
		if (!empty($data['marital_state'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".marital_state", $data['marital_state']);
		}
		if (!empty($data['threesome'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".threesome", $data['threesome']);
		}
		if (!empty($data['anal'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".anal", $data['anal']);
		}
		if (!empty($data['group'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".group", $data['group']);
		}
		if (!empty($data['bdsm'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".bdsm", $data['bdsm']);
		}
		if (!empty($data['swallow'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".swallow", $data['swallow']);
		}
		if (!empty($data['cum'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".cum", $data['cum']);
		}
		if (!empty($data['oral'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".oral", $data['oral']);
		}
		if (!empty($data['piss'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".piss", $data['piss']);
		}
		if (!empty($data['sex_massage'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".sex_massage", $data['sex_massage']);
		}
		if (!empty($data['petting'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".petting", $data['petting']);
		}
		if (!empty($data['fisting'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".fisting", $data['fisting']);
		}
		if (!empty($data['deepthroat'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".deepthrought", $data['deepthroat']);
		}

		if (!empty($data['city']) && !empty($data['district']) && !empty($data['region'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".cityID", $data['city']);
		} else if (!empty($data['city']) && !empty($data['district'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".districtID", $data['district']);
		}
		if (!empty($data['region'])) {
			$sel->where(self::COLUMN_PROPERTY_ID . ".regionID", $data['region']);
		}
		return $sel;
	}

	/**
	 * Zaregistruje uživatele
	 * @param array $data Data obsahující nejen informace o páru, musí se
	 * proto probrat.
	 */
	public function register($data, $propertyID) {
		$sel = $this->getTable();

		$user[self::COLUMN_PROPERTY_ID] = $propertyID;
		$user[self::COLUMN_ROLE] = $data->role;
		$user[self::COLUMN_LAST_ACTIVE] = new \Nette\DateTime;
		$user[self::COLUMN_CREATED] = new \Nette\DateTime;
		$user[self::COLUMN_EMAIL] = $data->email;
		$user[self::COLUMN_USER_NAME] = $data->user_name;
		$user[self::COLUMN_PASSWORD] = $data->passwordHash;
		$user{self::COLUMN_CONFIRMED} = $data[self::COLUMN_CONFIRMED];

		$newUser = $sel->insert($user);

		$newUser->update(array(
			self::COLUMN_CONFIRMED => $data[self::COLUMN_CONFIRMED] . $newUser->id
		));

		return $newUser;
	}

	/**
	 * Vrátí nepotvrzené uživatele
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUnconfirmed() {
		return $this->getUsersByRole(Authorizator::ROLE_UNCONFIRMED_USER);
	}

	/**
	 * Vrátí část nepotvrzených uživatelů podle požadavků paginatoru
	 * @param type int $limit Počet uživatelů
	 * @param type int $offset Velikost množiny pro výběr
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUnconfirmedLimit($limit, $offset = 0) {
		$sel = $this->getUsersByRole(Authorizator::ROLE_UNCONFIRMED_USER);
		$sel->limit($limit, $offset);
		return $sel;
	}

	/**
	 * Vrátí všechny uživatele v roli user
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUsers() {
		return $this->getUsersByRole(Authorizator::ROLE_USER);
	}

	/**
	 * Vrátí část uživatelů s rolí user podle požadavků paginatoru
	 * @param type int $limit Počet uživatelů
	 * @param type int $offset Velikost množiny pro výběr
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleUsersLimit($limit, $offset = 0) {
		$sel = $this->getUsersByRole(Authorizator::ROLE_USER);
		$sel->limit($limit, $offset);
		return $sel;
	}

	/**
	 * Vrátí všechny uživatele v roli user
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleAdmin() {
		return $this->getUsersByRole(Authorizator::ROLE_ADMIN);
	}

	/**
	 * Vrátí část uživatelů s rolí admin podle požadavků paginatoru
	 * @param type int $limit Počet uživatelů
	 * @param type int $offset Velikost množiny pro výběr
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleAdminLimit($limit, $offset = 0) {
		$sel = $this->getUsersByRole(Authorizator::ROLE_ADMIN);
		$sel->limit($limit, $offset);
		return $sel;
	}

	/**
	 * Vrátí všechny uživatele v roli superadmin
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleSuperadmin() {
		return $this->getUsersByRole(Authorizator::ROLE_SUPERADMIN);
	}

	/**
	 * Vrátí část uživatelů s rolí superadmin podle požadavků paginatoru
	 * @param type int $limit Počet uživatelů
	 * @param type int $offset Velikost množiny pro výběr
	 * @return Nette\Database\Table\Selection
	 */
	public function getInRoleSuperadminLimit($limit, $offset = 0) {
		$sel = $this->getUsersByRole(Authorizator::ROLE_SUPERADMIN);
		$sel->limit($limit, $offset);
		return $sel;
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
		$user = parent::find($userID);
		$userProperty = $this->findProperties($userID);

		$baseUserData = array(
			'Jméno' => $user->user_name,
			'První věta' => $userProperty->first_sentence,
			/* 'Naposledy online' => $user->last_active, */
			'Druh uživatele' => UserBaseDao::getTranslateUserProperty($userProperty->type),
			/* 'Vytvoření profilu' => $user->created, */
			/* 'Email' => $user->email, */
			'O mně' => $userProperty->about_me,
		);
		$baseData = $this->getBaseData($userProperty);
		$other = $this->getOtherData($userProperty);
		$sex = $this->getSex($userProperty);
		$seek = $this->getWantToMeet($userProperty);
		return $baseUserData + $baseData + $other + $sex + $seek;
	}

	/**
	 * Vrací zkrácené info o uživateli
	 * @param int $userID ID uživatele
	 * @return bool|Database\Table\IRow
	 */
	public function getUserShortInfo($userID) {
		$userProperty = $this->findProperties($userID);
		$userShortInfo = array(
			'Druh uživatele' => UserBaseDao::getTranslateUserProperty($userProperty->type),
			'Stav' => UserBaseDao::getTranslateUserState($userProperty->marital_state),
			'Věk' => $this->getAge($userProperty->age),
//			'Chtěl bych potkat' => UserBaseDao::getTranslateUserInterestedIn($userProperty->interested_in),
			'První věta' => $userProperty->first_sentence,
		);
		$seek = $this->getWantToMeet($userProperty);
		return $userShortInfo + $seek;
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

	/**
	 * Vrátí celkový počet userů
	 * @return type
	 */
	public function getTotalCount() {
		return $this->getTable()->count();
	}

	/**
	 * Získá id a username lidí v tabulce
	 * @return Nette\Database\Table\Selection
	 */
	public function getUsernameAndId() {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_ID . ", " . self::COLUMN_USER_NAME);
		return $sel;
	}

	/**
	 * Získá lidi pro autocomplete, vynechá ty, kteří jsou již přidaní
	 * @param array $alreadyAllowed seznam přidaných uživatelů
	 * @return \Nette\Database\Table\Selection
	 */
	public function getUsernameAndIdForAllowGallery($alreadyAllowed) {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_ID . ", " . self::COLUMN_USER_NAME);
		$sel->where(self::COLUMN_ID . ' NOT', $alreadyAllowed);
		return $sel;
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

	public function setCouple($userID, $coupleID) {
		$sel = $this->getTable();

		$sel->wherePrimary($userID);
		$sel->update(array(
			self::COLUMN_COUPLE_ID => $coupleID
		));
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
			self::COLUMN_PASSWORD => $password
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

	/**
	 * Vrátí uživatele podle kategorií.
	 * @param array $categoryIDs
	 * @param int $meID Moje ID - id uživatele.
	 */
	public function getByCategories($categoryIDs, $meID) {
		$sel = $this->getTable();
		$sel->where("." . UserDao::COLUMN_PROPERTY_ID . "." . UserPropertyDao::COLUMN_PREFERENCES_ID . " IN", $categoryIDs);
		/* nevezme sam sebe */
		$sel->where(self::TABLE_NAME . "." . self::COLUMN_ID . " != ?", $meID);
		/* blokovaní uživatelé tohoto uživatele */
		$blokedUsers = $this->createSelection(UserBlokedDao::TABLE_NAME);
		$blokedUsers->where(UserBlokedDao::COLUMN_OWNER_ID, $meID);
		if ($blokedUsers->count(UserBlokedDao::COLUMN_ID)) {
			$sel->where(self::TABLE_NAME . "." . self::COLUMN_ID . " NOT IN", $blokedUsers);
		}
		return $sel;
	}

	/**
	 * Vrátí blokovaný uživatele konkrétního uživatele.
	 * @param int $ownerID ID uživatele, který blokuje jiného.
	 * @return Nette\Database\Table\Selection
	 */
	private function getBlokedUsers($ownerID) {
		$sel = $this->createSelection(UserBlokedDao::TABLE_NAME);
		$sel->where(UserBlokedDao::COLUMN_OWNER_ID, $ownerID);
		return $sel;
	}

	/**
	 * označí ověřeného uživatele
	 * @param type $userID ID uživatele
	 */
	public function verify($userID) {
		$sel = $this->getTable();
		$sel->wherePrimary($userID);
		$sel->update(array(self::COLUMN_VERIFIED => 1));
	}

}
