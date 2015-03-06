<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 5.3.2015
 */

namespace NetteExt\DBMover;

use POS\Model\DatabaseDao;
use POS\Model\UserDao;
use POS\Model\GalleryDao;
use POS\Model\UserGalleryDao;

/**
 * Nahraje data z ArrayHash do databáze
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DBDataInstaler {

	/**
	 * @var DatabaseDao Pro komunikaci s DB.
	 */
	private $databaseDao;

	public function __construct($databaseDao) {
		$this->databaseDao = $databaseDao;
	}

	public function install(array $tables) {
		foreach ($tables as $tableName => $tableData) {
			if ($tableName == 'files') {
				continue;
			}

			$this->insertRows($tableName, $tableData);
		}
	}

	private function insertRows($tableName, $tableData) {
		$this->databaseDao->begginTransaction();

		$sel = $this->databaseDao->createSelection($tableName);
		foreach ($tableData as $row) {
			switch ($tableName) {
				case UserDao::TABLE_NAME:
					$row = (array) $row;
					$row[UserDao::COLUMN_PROFIL_PHOTO_ID] = null;
					break;
				case GalleryDao::TABLE_NAME:
					$row = (array) $row;
					$row[GalleryDao::COLUMN_LAST_IMAGE_ID] = null;
					break;
				case UserGalleryDao::TABLE_NAME:
					$row = (array) $row;
					$row[UserGalleryDao::COLUMN_BEST_IMAGE_ID] = null;
					$row[UserGalleryDao::COLUMN_LAST_IMAGE_ID] = null;
					break;
			}
			$sel->insert($row);
		}

		$this->databaseDao->endTransaction();
	}

}
