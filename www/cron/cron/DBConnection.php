<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 2.3.2015
 */

namespace Cron;

use Nette\Database\Connection;
use Nette\Database\Context;

/**
 * Vrací připojení k databázi
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DBConnection {

	/**
	 * Vrací nové připojení k databázi.
	 * @return Nette\Database\Connection
	 */
	public static function getConnection() {
		$driver = 'mysql';

//		$host = '127.0.0.1';
//		$dbname = 'datenodecz01';
//		$user = 'datenodecz001';
//		$password = 'a10b0618p';
//		$host = 'localhost';
//		$dbname = 'pos_cron_emails';
//		$user = 'root';
//		$password = 'root';

		$dsn = "$driver://host=$host;dbname=$dbname;";





		$connection = new Connection($dsn, $user, $password);

		return $connection;
	}

	/**
	 * Vrací nový objekt pro práci s databází.
	 * @return Nette\Database\Context
	 */
	public static function getDatabase() {
		$connection = self::getConnection();
		$database = new Context($connection);

		return $database;
	}

}
