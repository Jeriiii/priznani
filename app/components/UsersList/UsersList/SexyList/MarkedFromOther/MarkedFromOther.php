<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Zobrazuje seznam uživatelů, které uživatele označili jako sexy
 *
 * @author Petr Kukrál
 */

namespace POSComponent\UsersList\SexyList;

use POS\Model\YouAreSexyDao;
use POS\Model\PaymentDao;

class MarkedFromOther extends BaseSexyList {

	/**
	 * @var \POS\Model\PaymentDao
	 * @inject
	 */
	public $paymentDao;

	public function __construct(PaymentDao $paymentDao, YouAreSexyDao $youAreSexyDao, $userID, $parent, $name) {
		parent::__construct($youAreSexyDao, $userID, $parent, $name);
		$this->paymentDao = $paymentDao;
	}

	public function render() {
		parent::render();
		$this->template->isUserPaying = TRUE; //$this->paymentDao->isUserPaying($this->userID);
		$this->template->countSexy = $this->youAreSexyDao->countToUser($this->userID);
		$this->renderTemplate(dirname(__FILE__) . "/markedFromOther.latte");
	}

	public function getSnippetName() {
		return "sexyList";
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset) {
		$sexyUsers = $this->youAreSexyDao->getAllToUser($this->userID, $this->limit, $offset);
		$this->template->sexyUsers = $sexyUsers;
	}

}
