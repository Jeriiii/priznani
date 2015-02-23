<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;
use POS\Model\UserDao;

/**
 * Spočítá statistiky uživatelů podle druhu účtu (muž, žena, pár ...)
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class PeopleBySexStatistics extends Statistics {

	/** @var \POS\Model\UserDao @inject */
	private $userDao;

	public function __construct(UserDao $userDao) {
		$this->userDao = $userDao;
	}

	/**
	 * Vrátí počet registrací v daném časovém intervalu.
	 * @param string $typeOfInterval Den, Týden, Měsíc ...
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @return \Nette\Database\Table\Selection
	 */
	public function countRegByInterval() {
		$sexs = $this->userDao->getUsersBySex();
		$sexsContainer = array();
		$sexsTemp = new \Nette\ArrayHash;

		$names = UserDao::getUserPropertyOption();

		foreach ($sexs as $sex) {
			$sexsTemp = new \Nette\ArrayHash;
			$sexsTemp->name = $names[$sex->name];
			$sexsTemp->count = $sex->countItems;

			$sexsContainer[] = $sexsTemp;
		}

		return $sexsContainer;
	}

	protected function getByIntervals(DateTime $fromDate, $countDates, $typeOfInterval = self::DAY) {
		$data = $this->countRegByInterval();
		return $data;
	}

}
