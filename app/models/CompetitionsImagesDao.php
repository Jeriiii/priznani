<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class CompetitionsImagesDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "competitions_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_IMAGE_ID = "imageID";
	const COLUMN_USER_ID = "userID";
	const COLUMN_COMPETITION_ID = "competitionID";
	const COLUMN_PHONE = "phone";
	const COLUMN_NAME = "name";
	const COLUMN_SURNAME = "surname";
	const COLUMN_ALLOWED = "allowed";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function getByCompetitions($competitionID) {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_IMAGE_ID);
		$sel->where(self::COLUMN_COMPETITION_ID, $competitionID);
		return $sel->fetchPairs(self::COLUMN_IMAGE_ID, self::COLUMN_IMAGE_ID);
	}

	public function findByApproved($competitionID) {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_ALLOWED . ", " . self::COLUMN_COMPETITION_ID . ", imageID.*");
		$sel->where(self::COLUMN_COMPETITION_ID, $competitionID);
		$sel->where(self::COLUMN_ALLOWED, 1);
		$sel->order('imageID.id DESC');
		return $sel->fetch();
	}

	public function findByImgId($imageID) {
		$sel = $this->getTable();
		$sel->where('imageID', $imageID);
		return $sel->fetch();
	}

}
