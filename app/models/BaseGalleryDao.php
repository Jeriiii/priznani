<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

use POS\Model\UserGalleryDao;

/**
 * Slouží pro základní operace s galerií
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BaseGalleryDao extends AbstractDao {
	/*	 * ****************************** UPDATE **************************** */

	/**
	 * Změní poslední vložený obrázek.
	 * @param int $galleryID ID galerie
	 * @param type $lastImageID ID posledního vloženého obrázku.
	 */
	public function updateLastImage($galleryID, $lastImageID, $columnName = UserGalleryDao::COLUMN_LAST_IMAGE_ID) {
		$sel = $this->getTable();
		$sel->wherePrimary($galleryID);
		$sel->update(array(
			$columnName => $lastImageID
		));
	}

}
