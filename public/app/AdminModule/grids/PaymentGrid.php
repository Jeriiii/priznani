<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 *  UsersGrid pro editaci uživatelů (změna práv, smazání) a filtrování dle emailu a nicku
 *
 * @author Christine Baierová
 */

namespace POS\Grids;

use Grido\Grid;
use POS\Model\PaymentDao;

class PaymentGrid extends Grid {

	/** @var \POS\Model\PaymentDao */
	public $paymentDao;

	public function __construct(PaymentDao $paymentDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->paymentDao = $paymentDao;

		$this->setModel($paymentDao->getPaymentsReview());

		$this->addColumns();
		$this->addActionHrefs();
	}

	/**
	 * Přidá sloupce do grida.
	 */
	private function addColumns() {
		$this->addColumnText("id", "ID")
			->setDefaultSort('ASC');

		$this->addColumnText("user_name", "Username")
			->setFilterText()
			->setColumn('userID.user_name');

		$this->addColumnEmail("email", "Email")
			->setFilterText()
			->setColumn('userID.email');

		$this->addColumnDate("from", "Od")
			->setFilterText();

		$this->addColumnDate("to", "Do")
			->setFilterText();
	}

	/**
	 * Přidá tlačítka s akcemi do grida.
	 */
	private function addActionHrefs() {
		$this->addActionHref('delete_payment', 'Smazat', 'deletePayment!')
			->setConfirm(function($item) {
				return "Opravdu chcete smazat {$item->user_name} ?";
			});
	}

}
