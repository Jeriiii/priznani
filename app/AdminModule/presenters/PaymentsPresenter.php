<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PaymentsPresenter
 *
 * @author Daniel
 */

namespace AdminModule;

class PaymentsPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\PaymentDao
	 * @inject
	 */
	public $paymentDao;

	/**
	 * Komponenta grido vykresluje přehledně tabulky s daty o platbách
	 * @param type $name
	 */
	protected function createComponentGrid($name) {
		$grid = new \Grido\Grid($this, $name);
		$grid->setModel($this->paymentDao->getPaymentsData());

		/* sloupce komponenty */
		$grid->addColumnText("id", "ID")
			->setDefaultSort('ASC');

		$grid->addColumnText("user_name", "Username")
			->setFilterText()
			->setColumn('userID.user_name');

		$grid->addColumnEmail("email", "Email")
			->setFilterText()
			->setColumn('userID.email');

		$grid->addColumnDate("create", "Create");
		/* konec sloupců */
	}

}
