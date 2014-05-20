<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Uživatelé UsersDao
 * slouží k práci s uživateli
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UsersDao extends UsersBaseDao {

	const TABLE_NAME = "users";

	/**
	 * Vrací tuto tabulku
	 * @return Nette\Database\Table\Selection
	 */
	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrácí všechna data o uživateli, nikoliv o partnerovi
	 * @param int $userID ID uživatele
	 * @return type
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

}
