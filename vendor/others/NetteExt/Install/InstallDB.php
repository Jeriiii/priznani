<?php

namespace NetteExt\Install;

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of SQL
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use NetteExt\Dir\Dir;

class InstallDB {
	/* složky */

	const DIR_DATABASE = "database";
	const DIR_STABLE = "stable";
	const DIR_PATCH = "patch";

	/* tabulky */
	const TABLE_POS = "pos";
	const TABLE_POS_TEST = "postest";

	public static function sqlInstall($dbConection) {
		self::executeSql($dbConection, self::TABLE_POS);
		self::executeSql($dbConection, self::TABLE_POS_TEST);

		echo "Databáze " . self::TABLE_POS . " a " . self::TABLE_POS_TEST . " byly úspěšně nainstalovány <br />";
	}

	private static function executeSql($dbConection, $dbName) {
		$databasePath = WWW_DIR . "/../" . self::DIR_DATABASE;

		/* smazání a opětovné vytvoření */
		$sql[] = self::getStartSql($dbName);

		/* stable verze */
		$sql = $sql + self::getSql(self::DIR_STABLE, $databasePath);

		/* soubory patch */
		$sql = $sql + self::getSql(self::DIR_PATCH, $databasePath);

		/* Soubory z vývoje */
		$sql = $sql + self::getSql("../" . self::DIR_DATABASE, $databasePath);

		foreach ($sql as $s) {
			$dbConection->query($s);
		}
	}

	/**
	 * Vrátí obsah souborů ze složky
	 * @param string $dirName Název složky.
	 * @param string $path Cesta ke složce
	 * @return type
	 */
	public static function getSql($dirName, $path) {
		$stablePath = $path . "/" . $dirName;
		$stableDir = new Dir($stablePath);
		$stableDir->sortOutFilles(0, ".sql");
		$sql = $stableDir->getFilles();
		return $sql;
	}

	private static function getStartSql($dbName) {
		$sql = "DROP DATABASE `" . $dbName . "`;";
		$sql .= "CREATE DATABASE `" . $dbName . "`;";
		$sql .= "USE `" . $dbName . "`;";
		return $sql;
	}

}
