<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UsersCompetitionsDao extends AbstractDao {

	/** @var Nette\Database */
	protected $database;

	const TABLE_NAME = "users_competitions";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "description";
	const COLUMN_IMAGE_URL = "imageUrl";
	const COLUMN_CURRENT = "current";
	const COLUMN_LAST_IMAGE_ID = "lastImageID";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí poslední soutěž
	 * @return Nette\Database\Table\Selection
	 */
	public function findLast() {
		$sel = $this->getTable();
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel->fetch();
	}

	/**
	 * Updatne lastImageID
	 * @param int $competitionID ID soutěže, kterou chceme updatnout
	 * @param int $imageID ID obrázku pro lastImageID
	 */
	public function updateLastImage($competitionID, $imageID) {
		$sel = $this->getTable();
		$sel->get($competitionID);
		$sel->update(array(
			self::COLUMN_LAST_IMAGE_ID => $imageID
		));
	}

	/**
	 * Vrátí jméno a ID poslední soutěže
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getLastCompetitionNameAndId() {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_ID . ', ' . self::COLUMN_NAME);
		$sel->order(self::COLUMN_ID . ' DESC');
		return $sel->fetch();
	}

}
