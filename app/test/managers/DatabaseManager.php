<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

/**
 * Used for database work during testing
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
use NetteExt\Install\DB\InstallDB;
use POS\Model\DatabaseDao;
use Nette\Database\Context;

class DatabaseManager {

	/**
	 * Names of folders in sqlFolder
	 */
	private static $ON_START_FOLDER_NAME = "init"; //executes once at start
	private static $FEATURE_START_FOLDER_NAME = "featurestart"; //executes at start of every feature
	private static $FEATURE_END_FOLDER_NAME = "featureend"; //executes in the end of every feature
	/**
	 *
	 * @var string Absolute path to folder with scripts
	 */
	private $sqlFolder;

	/**
	 *
	 * @var \Nette\Database\Context database context to work with
	 */
	private $database;

	/**
	 * @var \POS\Model\DatabaseDao
	 */
	public $databaseDao;

	/**
	 *
	 * @param string $sqlFolder Absolute path to folder with scripts
	 */
	function __construct($sqlFolder, Context $database, DatabaseDao $databaseDao) {
		$this->sqlFolder = $sqlFolder;
		$this->database = $database;
		$this->databaseDao = $databaseDao;
	}

	/**
	 * Returns instance of database manager
	 * @return DatabaseManager
	 */
	public static function getInstance() {
		return $GLOBALS['container']->getByType(__CLASS__);
	}

	/**
	 * Recreates current database (drops all tables and data)
	 */
	function clearDatabase() {
		$query = $this->database->query('SELECT DATABASE()');
		$name = $query->fetchField();
		$this->database->query('DROP DATABASE ' . $name);
		$this->database->query('CREATE DATABASE ' . $name . ' CHARACTER SET utf8');
		$this->database->query('USE ' . $name);
	}

	/**
	 * Executes initialization scripts from init folder
	 */
	function initScripts() {
		$installDB = new InstallDB($this->databaseDao);
		//$installDB->instalPostestDb();
		$installDB->dataTestDb();
		$this->executeAllSqlInFolder($this->sqlFolder . '/' . self::$ON_START_FOLDER_NAME);
	}

	/**
	 * Executes scripts from feature start folder
	 */
	function featureStartScripts() {
		$this->executeAllSqlInFolder($this->sqlFolder . '/' . self::$FEATURE_START_FOLDER_NAME);
	}

	/**
	 * Executes scripts from feature end folder
	 */
	function featureEndScripts() {
		$this->executeAllSqlInFolder($this->sqlFolder . '/' . self::$FEATURE_END_FOLDER_NAME);
	}

	/**
	 * Executes all sql files from folder
	 * @param string $path path to folder
	 */
	function executeAllSqlInFolder($path) {
		$files = scandir($path);
		foreach ($files as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
				$this->executeSqlFile($path . '/' . $file);
			}
		}
	}

	/**
	 * Executes given SQL file
	 * http://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php
	 * @param string $path absolute path to file
	 */
	function executeSqlFile($path) {

		//load file
		$content = file_get_contents($path);
		$commands = $this->parseSqlContent($content);
		$this->runSqlCommands($commands);
	}

	/**
	 * Parses SQL into an array
	 * @param string $content text of file
	 * @return array commands of sql file
	 */
	function parseSqlContent($content) {
		//delete comments
		$lines = explode("\n", $content);
		$commands = '';
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line && !$this->startsWith($line, '--')) {
				$commands .= $line . "\n";
			}
		}

		//convert to array
		return explode(";", $commands);
	}

	/**
	 * Executes sql commands in array
	 * @param array $commands array of commands
	 */
	function runSqlCommands(array $commands) {
		//run commands
		foreach ($commands as $command) {
			if (trim($command)) {
				$this->database->query($command);
			}
		}
	}

	/**
	 * Determines if haystack starts with needle
	 * @param string $haystack what starts
	 * @param string $needle starts with what
	 * @return boolean starts or not starts
	 */
	function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

}
