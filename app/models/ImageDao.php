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
class ImageDao extends AbstractDao {

	const TABLE_NAME = "images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_GALLERY_ID = "galleryID";
	const COLUMNT_ORDER = "order";
	const COLUMN_APPROVED = "approved";
	const COLUMN_GAL_SCRN_WIDTH = "widthGalScrn";
	const COLUMN_GAL_SCRN_HEIGHT = "heightGalScrn";

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Vrátí obrázky v dané galerii a seřadí je podle sloupce order
	 * @param int $galleryID ID galerie.
	 * @return Nette\Database\Table\Selection
	 */
	public function getInGallery($galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		$sel->order(self::COLUMNT_ORDER . " ASC");
		return $sel;
	}

	/**
	 * Vrátí jen schválené fotky z dané galerie.
	 * @param int $galleryID ID galerie.
	 * @return \Nette\Database\Table\Selection
	 */
	public function getApproved($galleryID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_APPROVED, 1);
		$sel->where(self::COLUMN_GALLERY_ID, $galleryID);
		$sel->order(self::COLUMN_ID . " DESC");
		return $sel;
	}

	/**
	 * Vrátí poslední schválenou fotku z dané galerie.
	 * @param int $galleryID ID galerie.
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findByApproved($galleryID) {
		return $this->getApproved($galleryID)->fetch();
	}

	/*	 * **************************** UPDATE ******************************** */

	/**
	 * Schválí fotku.
	 * @param int $id Image ID.
	 */
	public function approve($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_APPROVED => 1
		));
	}

	/**
	 * Upraví výšku a šířku u SCREENU obrázku.
	 * @param type $imageID ID obrázku.
	 * @param type $imageWidth Výška obrázku.
	 * @param type $imageHeight Šířka obrázku.
	 */
	public function updateScrnWidthHeight($imageID, $imageWidth, $imageHeight) {
		$sel = $this->getTable();
		$sel->wherePrimary($imageID);
		$sel->update(array(
			self::COLUMN_GAL_SCRN_WIDTH => $imageWidth,
			self::COLUMN_GAL_SCRN_HEIGHT => $imageHeight
		));
	}

}
