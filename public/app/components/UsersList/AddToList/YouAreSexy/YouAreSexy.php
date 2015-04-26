<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Označí uživatele že je sexy
 *
 * @author Petr Kukrál
 */

namespace POSComponent\AddToList;

use POS\Model\YouAreSexyDao;
use POS\Model\UserPropertyDao;

class YouAreSexy extends AddToList {

	/** @var \POS\Model\YouAreSexyDao */
	public $youAreSexyDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;

	public function __construct(YouAreSexyDao $youAreSexyDao, UserPropertyDao $userPropertyDao, $userIDFrom, $userIDTo, $parent, $name) {
		parent::__construct($userIDFrom, $userIDTo, $parent, $name);
		$this->youAreSexyDao = $youAreSexyDao;
		$this->userPropertyDao = $userPropertyDao;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		if ($this->getDeviceDetector()->isMobile()) {
			$this->template->setFile(dirname(__FILE__) . '/mobileYouAreSexy.latte');
		} else {
			$this->template->setFile(dirname(__FILE__) . '/youAreSexy.latte');
		}
		$this->template->sexy = $this->youAreSexyDao->findByUsers($this->userIDFrom, $this->userIDTo);
		$this->template->render();
	}

	public function handleYouAreSexy() {
		try {
			$this->youAreSexyDao->addSexy($this->userIDFrom, $this->userIDTo);
		} catch (\POS\Exception\DuplicateRowException $e) {
			$this->flashMessage("Uživatel byl již označen.");
		}

		$this->redrawControl();
	}

}
