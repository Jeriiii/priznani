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
use POS\Model\UserImageDao;

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

	/**
	 * @var array Všechny tabulky, co mají kruhovou vazbu.
	 */
	private $circleRel = array(
		UserDao::TABLE_NAME => array(UserDao::COLUMN_PROFIL_PHOTO_ID),
		GalleryDao::TABLE_NAME => array(GalleryDao::COLUMN_LAST_IMAGE_ID),
		UserGalleryDao::TABLE_NAME => array(UserGalleryDao::COLUMN_BEST_IMAGE_ID, UserGalleryDao::COLUMN_LAST_IMAGE_ID),
		UserDao::TABLE_NAME => array(UserDao::COLUMN_PROFIL_PHOTO_ID),
	);

	public function __construct($databaseDao) {
		$this->databaseDao = $databaseDao;
	}

	public function install(array $tables) {

//		foreach ($tables as $tableName => $tableData) {
//			if ($tableName == 'files' || $tableName == 'users_fotos') {
//				continue;
//			}
//
//			$this->insertRows($tableName, $tableData);
//		}

		foreach ($tables as $tableName => $tableData) {
			if (!array_key_exists($tableName, $this->circleRel)) {
				continue;
			}

			$this->updateRows($tableName, $tableData);
		}
	}

	private function insertRows($tableName, $tableData) {
		$this->databaseDao->begginTransaction();

		$sel = $this->databaseDao->createSelection($tableName);
		foreach ($tableData as $row) {
			switch ($tableName) {
				case UserImageDao::TABLE_NAME:
					$row = (array) $row;
					$row[UserImageDao::COLUMN_CREATED] = null;
			}
			/* rozbití kruhových vazeb */
			if (array_key_exists($tableName, $this->circleRel)) {
				foreach ($this->circleRel[$tableName] as $rowName) {
					$row = (array) $row;
					$row[$rowName] = null;
				}
			}
			$sel->insert($row);
		}

		$this->databaseDao->endTransaction();
	}

	private function updateRows($tableName, $tableData) {
		$this->databaseDao->begginTransaction();

		foreach ($tableData as $row) {
			$sel = $this->databaseDao->createSelection($tableName);

			/* spojení kruhových vazeb */
			if (array_key_exists($tableName, $this->circleRel)) {
				$toUpdate = array();

				foreach ($this->circleRel[$tableName] as $rowName) {
					$toUpdate[$rowName] = $row[$rowName];
				}

				$sel->where('id', $row->id);
				$sel->update($toUpdate);
			}
		}

		$this->databaseDao->endTransaction();
	}

}
