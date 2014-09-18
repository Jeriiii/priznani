<?php

namespace NetteExt\Install\DB;

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

use NetteExt\Install\Messages;

/**
 * Description of SQL
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class InstallDB {
	/* tabulky */

	const TABLE_POS = "pos";
	const TABLE_POS_TEST = "postest";
	const TABLE_POS_CHAT = "poschat";

	/** @var \POS\Model\DatabaseDao */
	private $dbDao;

	/** @var Database\Context */
	private $dbConection;

	/** @var Messages Zprávy co se zobrazí uživateli */
	private $messages;

	/** @var boolean TRUE = Je instalace DB zaplá testovacím nástrojem? Jinak FALSE. */
	private $testingMode;

	public function __construct($dbDao, $testingMode, $messages = NULL) {
		$this->dbDao = $dbDao;
		$this->testingMode = $testingMode;
		$this->dbConection = $dbDao->getDatabase();
		if (isset($messages)) {
			$this->messages = $messages;
		} else {
			$this->messages = new Messages;
		}
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
		$sql = new Sql(self::TABLE_POS, $this->testingMode);
		$sql->setSqlAllDB();
		$this->executeSql($sql);

		$this->messages->addMessage("Databáze " . self::TABLE_POS . " byla úspěšně nainstalována");
	}

	/**
	 * Reinstaluje a naplní daty testovací databázi.
	 */
	public function instalPostestDb() {
		$sql = new Sql(self::TABLE_POS_TEST, $this->testingMode);
		$sql->setSqlAllDB();
		$this->executeSql($sql);

		$this->messages->addMessage("Databáze " . self::TABLE_POS_TEST . " byla úspěšně nainstalována");
	}

	/**
	 * Reinstaluje a naplní daty databázi chatu.
	 */
	public function instalPoschatDb() {
		$sql = new Sql(self::TABLE_POS_CHAT, $this->testingMode);
		$sql->setSqlChatDB();
		$this->executeSql($sql);

		$this->messages->addMessage("Databáze " . self::TABLE_POS_CHAT . " byla úspěšně nainstalována");
	}

	/**
	 * Obnový data z testovací DB na standartní.
	 */
	public function dataTestDb() {
		$this->recoveryData(self::TABLE_POS_TEST);
	}

	public function dataDb() {
		$this->recoveryData(self::TABLE_POS);
	}

	private function recoveryData($dbName) {
		$sql = new Sql($dbName, $this->testingMode);
		$sql->setData($this->dbDao);
		$this->executeSql($sql);

		$this->messages->addMessage("Data z databáze " . $dbName . " byla obnovena.");
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
