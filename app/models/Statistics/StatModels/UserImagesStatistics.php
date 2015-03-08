<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Statistics;

use Nette\DateTime;
use POS\Model\UserImageDao;

/**
 * Spočítá statistiky změn galerií ve streamu
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserImagesStatistics extends Statistics {

	/** @var \POS\Model\UserImageDao @inject */
	private $userImageDao;

	public function __construct(UserImageDao $userImageDao) {
		$this->userImageDao = $userImageDao;
	}

	/**
	 * Vrátí počet registrací v daném časovém intervalu.
	 * @param string $typeOfInterval Den, Týden, Měsíc ...
	 * @param DateTime $fromDate Počáteční datum, od kdy chci vědět registrace.
	 * @return \Nette\Database\Table\Selection
	 */
	public function countRegByInterval($typeOfInterval, $fromDate) {
		switch ($typeOfInterval) {
			case self::DAY:
				return $this->userImageDao->countByDay($fromDate);
			case self::MONTH:
				return $this->userImageDao->countByMonth($fromDate);
			default:
				throw new Exception('$typeOfInterval must by interval constant.');
		}
	}

}
