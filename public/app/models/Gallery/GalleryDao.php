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
class GalleryDao extends BaseGalleryDao {

	const TABLE_NAME = "galleries";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_NAME = "name";
	const COLUMN_DESCRIPTION = "description";
	const COLUMN_SEX_MODE = "sexmode";
	const COLUMN_PARTY_MODE = "partymode";
	const COMULN_COMPETITION = "competition";
	const COLUMN_LAST_IMAGE_ID = 'lastImageID';

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	public function updateNameDecripMode($id, $name, $description, $mode = "sex") {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_NAME => $name,
			self::COLUMN_DESCRIPTION => $description,
			self::COLUMN_SEX_MODE => 1,
			self::COLUMN_PARTY_MODE => 0
		));
	}

	/**
	 * Vrátí pouze galerii bez soutěží
	 * @return Nette\Database\Table\Selection
	 */
	public function getGallery() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEX_MODE, 1);
		$sel->where(self::COMULN_COMPETITION, 0);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí pouze soutěže bez ostatních galerií
	 * @return Nette\Database\Table\Selection
	 */
	public function getCompetition() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEX_MODE, 1);
		$sel->where(self::COMULN_COMPETITION, 1);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí galerii v danném módu
	 * @return Nette\Database\Table\Selection
	 */
	public function findLast() {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_SEX_MODE, 1);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel->fetch();
	}

}
