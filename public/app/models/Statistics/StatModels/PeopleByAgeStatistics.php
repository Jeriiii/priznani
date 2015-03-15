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
class PeopleByAgeStatistics extends Statistics {

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
	public function countByInterval() {
		$ages = array(
			array(
				'from' => 18,
				'to' => 25
			),
			array(
				'from' => 26,
				'to' => 30
			),
			array(
				'from' => 31,
				'to' => 35
			),
			array(
				'from' => 36,
				'to' => 40
			),
			array(
				'from' => 40,
				'to' => 50
			),
			array(
				'from' => 50,
				'to' => 200
			),
		);
		$agesContainer = array();

		foreach ($ages as $age) {
			$ageTemp = new \Nette\ArrayHash;
			$ageTemp->name = $age['from'] . '-' . $age['to'];

			$yearFrom = new DateTime();
			$yearFrom->modify('- ' . $age['from'] . ' years');
			$yearTo = new DateTime();
			$yearTo->modify('- ' . $age['to'] . ' years');

			$ageTemp->count = $this->userDao->getUsersByAge($yearFrom->format('Y'), $yearTo->format('Y'));
			$agesContainer[] = $ageTemp;
		}

		return $agesContainer;
	}

	protected function getByIntervals(DateTime $fromDate, $countDates, $typeOfInterval = self::DAY) {
		$data = $this->countByInterval();
		return $data;
	}

}
