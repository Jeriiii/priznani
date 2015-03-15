<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */
/**
 * Zobrazí výběr účtu - má se zobrazit ve vyskakovacím okénku, ale to se musí
 * už zajistit javascriptem. Zajišťuje všechny tři kroky.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace POSComponent;

use Nette\Application\UI\Form as Frm;

class Payment extends BaseProjectControl {

	const PAYMENT_TYPE_BLANK = 0;
	const PAYMENT_TYPE_BANK_ACCOUNT = 1;

	/* Rozlišují, na jakých templatách se uživ. nachází */

	public $accountChoose = FALSE;
	public $paymentChoose = FALSE;
	public $paymentComplete = FALSE;

	/** @var int Typ platby */
	public $paymentType = self::PAYMENT_TYPE_BLANK;

	public function __construct($parent, $name) {
		parent::__construct($parent, $name);
	}

	/**
	 * Vykresli šablonu.
	 */
	public function render() {
		if (!$this->presenter->isAjax()) {
			$this->accountChoose = TRUE;
		}
		$this->template->setFile(dirname(__FILE__) . "/payment.latte");
		$this->template->accountChoose = $this->accountChoose;
		$this->template->paymentChoose = $this->paymentChoose;
		$this->template->paymentComplete = $this->paymentComplete;
		$this->template->type = $this->paymentType;
		$this->template->render();
	}

	/**
	 * Zpracovává platbu
	 * @param string $type typ účtu premium|exclusive
	 */
	public function handlePaymentChoose($type) {
		$this->paymentChoose = TRUE;
		$this->redrawControl();
	}

	public function handlePaymentComplete() {
		$this->paymentComplete = TRUE;
		$this->redrawControl();
	}

	protected function createComponentChoosePaymentForm($name) {
		return new \POS\Forms\PaymentChooseForm($this, $name);
	}

}
