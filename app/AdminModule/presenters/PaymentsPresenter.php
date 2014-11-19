<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Description of PaymentsPresenter
 *
 * @author Daniel
 */

namespace AdminModule;

use POS\Forms\PaymentNewForm;
use POS\Grids\PaymentGrid;

class PaymentsPresenter extends AdminSpacePresenter {

	/**
	 * @var \POS\Model\PaymentDao
	 * @inject
	 */
	public $paymentDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * Komponenta grido vykresluje přehledně tabulky s daty o platbách
	 * @param type $name
	 */
	protected function createComponentGrid($name) {
		return new PaymentGrid($this->paymentDao, $this, $name);
	}

	protected function createComponentPaymentNewForm($name) {
		return new PaymentNewForm($this->userDao, $this->paymentDao, $this, $name);
	}

	public function handleDeletePayment($id) {
		$this->paymentDao->delete($id);

		$this->flashMessage("Platba byla smazána.");
		$this->redirect("this");
	}

}
