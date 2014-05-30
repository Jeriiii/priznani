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

}
