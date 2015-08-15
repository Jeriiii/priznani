<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 14.8.2015
 */

namespace POS\Model;

/**
 * Obrázky ke článkům v blogu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class BlogImageDao extends AbstractDao {

	const TABLE_NAME = "magazine_images";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_SUFFIX = 'suffix';
	const COLUMN_ARTICLE_ID = 'articleID';

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

}
