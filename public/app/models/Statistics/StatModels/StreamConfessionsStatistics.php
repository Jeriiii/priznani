<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;
use POS\Model\StreamDao;

/**
 * Spočítá statistiky přiznání ve streamu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class StreamConfessionsStatistics extends Statistics {

	/** @var \POS\Model\StreamDao @inject */
	private $streamDao;

	public function __construct(StreamDao $streamDao) {
		$this->streamDao = $streamDao;
	}

	/**
	 * Vrátí počet registrací v daném časovém intervalu.
	 * @param string $typeOfInterval Den, Týden, Měsíc ...
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @return \Nette\Database\Table\Selection
	 */
	public function countItemsByInterval($typeOfInterval, $fromDate) {
		switch ($typeOfInterval) {
			case self::DAY:
				return $this->streamDao->countConfessionByDay($fromDate);
			case self::MONTH:
				return $this->streamDao->countConfessionByMonth($fromDate);
			default:
				throw new Exception('$typeOfInterval must by interval constant.');
		}
	}

}
