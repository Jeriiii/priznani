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

class YouAreSexy extends AddToList {

	/** @var \POS\Model\YouAreSexyDao */
	public $youAreSexyDao;

	public function __construct(YouAreSexyDao $youAreSexyDao, $userIDFrom, $userIDTo, $parent, $name) {
		parent::__construct($userIDFrom, $userIDTo, $parent, $name);
		$this->youAreSexyDao = $youAreSexyDao;
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/youAreSexy.latte');
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
