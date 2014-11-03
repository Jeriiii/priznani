<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Forms;

use Nette\Application\UI\Form\BaseForm;
use POSComponent\Payment;

/**
 * Přepíná typ platby
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class PaymentChooseForm extends BaseForm {

	/** @var Payment Control Přímo nadřazená komponenta. */
	public $parent;

	public function __construct($parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->parent = $parent;
		$this->ajax();

		$types = array(
			Payment::PAYMENT_TYPE_BANK_ACCOUNT => "platba přes účet"
		);
		$this->addSelect("type", "Druh platby", $types)
			->setPrompt("- vyberte typ platby -");
		$this->addSubmit('send', 'vybrat');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(PaymentChooseForm $form) {
		$values = $form->getValues();

		if ($this->presenter->isAjax()) {
			$form->clearFields();
			$this->parent->paymentChoose = TRUE;
			$this->parent->paymentType = $values->type;
			$this->parent->redrawControl('payment');
		} else {
			$this->presenter->redirect('this');
		}
	}

}
