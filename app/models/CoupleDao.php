<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Páry
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CoupleDao extends UserBaseDao {

	const TABLE_NAME = "couple";

	/* Column name */
	const COLUMN_ID = "id";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrácí všechna data o partnerovi bez dat o uživateli.
	 * @param int $id ID partnera
	 * @return array
	 */
	public function getPartnerData($id) {
		$user = $this->find($id);
		$baseData = $this->getBaseData($user);
		$sex = $this->getSex($user);

		return $baseData + $sex;
	}

}
