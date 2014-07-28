<?php

namespace NetteExt\Install\DB;

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of SQL
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class InstallDB {
	/* tabulky */

	const TABLE_POS = "pos";
	const TABLE_POS_TEST = "postest";

	/** @var \POS\Model\DatabaseDao */
	private $dbDao;

	/** @var Database\Context */
	private $dbConection;

	/**
	 * @param \POS\Model\DatabaseDao $dbDao
	 */
	public function __construct($dbDao) {
		$this->dbDao = $dbDao;
		$this->dbConection = $dbDao->getDatabase();
	}

	/**
	 * Reinstaluje obě databáze a naplní je daty.
	 */
	public function installAll() {
		$this->installPosDb();
		$this->instalPostestDb();
	}

	/**
	 * Reinstaluje a naplní daty normální databázi.
	 */
	public function installPosDb() {
		$sql = new Sql(self::TABLE_POS);
		$sql->setSqlAllDB();
		$this->executeSql($sql);

		echo "Databáze " . self::TABLE_POS . " byla úspěšně nainstalována <br />";
	}

	/**
	 * Reinstaluje a naplní daty testovací databázi.
	 */
	public function instalPostestDb() {
		$sql = new Sql(self::TABLE_POS_TEST);
		$sql->setSqlAllDB();
		$this->executeSql($sql);

		echo "Databáze " . self::TABLE_POS_TEST . " byla úspěšně nainstalovány <br />";
	}

	/**
	 * Obnový data z testovací DB na standartní.
	 */
	public function dataPostestDb() {
		$sql = new Sql(self::TABLE_POS_TEST);
		$sql->setData($this->dbDao);
		$this->executeSql($sql);

		echo "Data z databáze " . self::TABLE_POS_TEST . " byla obnovena.";
	}

	/**
	 * Vykoná v jedné transakci celé SQL.
	 * @param \NetteExt\Install\DB\Sql $sql Sql, co se má vykonat.
	 */
	private function executeSql($sql) {
		$this->dbDao->begginTransaction();

		foreach ($sql->getSql() as $s) {
			$this->dbConection->query($s);
		}

		$this->dbDao->endTransaction();
	}

}
