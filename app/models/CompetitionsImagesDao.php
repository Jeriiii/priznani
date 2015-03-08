<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Model\UserImageDao;

/**
 * CompetitionImagesDao
 * slouží k práci se soutěžníma obrázkama
 *
 * @author Daniel Holubář
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

	/**
	 * Vrátí indexy obrázků podle soutěže v poli
	 * @param int $competitionID ID soutěže, z které jsou obrázky vybrány
	 * @return array Pole indexů obrázků z soutěže
	 */
	public function getApprovedByComp($competitionID) {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_IMAGE_ID);
		$sel->where(self::COLUMN_COMPETITION_ID, $competitionID);
		$sel->where(self::COLUMN_ALLOWED, 1);
		return $sel->fetchPairs(self::COLUMN_IMAGE_ID, self::COLUMN_IMAGE_ID);
	}

	/**
	 * Vrátí jedne obrázek ze schválených dané soutěže, který se nalézá v user_images
	 * @param int $competitionID ID soutěže
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByApproved($competitionID) {
		$sel = $this->getTable();
		$sel->select(self::COLUMN_ALLOWED . ", " . self::COLUMN_COMPETITION_ID . ", imageID.*");
		$sel->where(self::COLUMN_COMPETITION_ID, $competitionID);
		$sel->where(self::COLUMN_ALLOWED, 1);
		$sel->order('imageID.id DESC');
		return $sel->fetch();
	}

	/**
	 * Vrátí obrázek z competition_images podle imageID
	 * @param int $imageID ID obrázku z tabulky user_images
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByImgId($imageID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_IMAGE_ID, $imageID);
		return $sel->fetch();
	}

	/**
	 * Vyhledá, zda je obrázek již umístěn v soutěži
	 * @param int $imageID ID hledaného obrázku
	 * @param int $competitionID ID soutěže
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findByImgAndCmpId($imageID, $competitionID) {
		$sel = $this->getTable();
		$sel->where(array(
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_COMPETITION_ID => $competitionID,
		));
		return $sel->fetch();
	}

	/**
	 * Vloží obrázek do soutěže
	 * @param int $imageID ID obrázku, který bude vložen
	 * @param int $userID ID uživatele, jemuž patří obrázek
	 * @param int $competitionID ID soutěže, do které bude obrázek vložen
	 */
	public function insertImageToCompetition($imageID, $userID, $competitionID) {
		$sel = $this->getTable();
		$sel->get($competitionID);
		$sel->insert(array(
			self::COLUMN_IMAGE_ID => $imageID,
			self::COLUMN_COMPETITION_ID => $competitionID,
			self::COLUMN_USER_ID => $userID
		));
	}

	/**
	 * Vrátí neschválené fotky
	 * @return Nette\Database\Table\Selection
	 */
	public function getUnapproved() {
		$sel = $this->getTable();
		return $sel->where(self::COLUMN_ALLOWED, 0);
	}

	/**
	 * Schválí soutěžní obrázek i obrázek v galerii
	 * @param int $imageID ID obrázku ke schválení
	 */
	public function acceptImage($imageID) {
		$sel = $this->getTable();
		$sel->wherePrimary($imageID);
		$sel->update(array(
			self::COLUMN_ALLOWED => 1
		));

		//schválení fotky i v user_images
		$comImage = $sel->fetch();
		$gallImage = $comImage->image;
		$gallImage->update(array(
			UserImageDao::COLUMN_APPROVED => 1
		));

		return $comImage;
	}

	/**
	 * Schválí soutěžní obrázek i obrázek v galerii jako intimní
	 * @param int $imageID ID obrázku ke schválení
	 */
	public function acceptImageIntim($imageID) {
		$sel = $this->getTable();
		$sel->wherePrimary($imageID);
		$sel->update(array(
			self::COLUMN_ALLOWED => 1
		));

		//schválení fotky i v user_images
		$comImage = $sel->fetch();
		$gallImage = $comImage->image;
		$gallImage->update(array(
			UserImageDao::COLUMN_APPROVED => 1,
			UserImageDao::COLUMN_INTIM => 1
		));

		return $comImage;
	}

}
