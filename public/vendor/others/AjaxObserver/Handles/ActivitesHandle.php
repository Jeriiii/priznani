<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Ajax;

use POS\Model\ActivitiesDao;

/**
 * Příklad Handlu pro AjaxObserver
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ActivitesHandle extends \Nette\Object implements IObserverHandle {

	/**
	 * @var \POS\Model\ActivitiesDao
	 * @inject
	 */
	public $activitiesDao;
	private $userId;

	public function __construct(ActivitiesDao $dao, $userId) {
		$this->activitiesDao = $dao;
		$this->userId = $userId;
	}

	/**
	 * Implementace rozhrani IObserverHandle
	 */
	public function getData() {
		$count = $this->activitiesDao->getCountOfUnviewed($this->userId);
		return $count;
	}

}
