<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POS\Forms;

use Nette\Forms\Form;
use Nette\Application\UI\Form\BaseForm;
use POS\Model\UserDao;
use POS\Model\PaymentDao;
use Nette\DateTime;

/**
 * Popis formuláře
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class PaymentNewForm extends BaseForm {

	/** @var \POS\Model\UserDao */
	public $userDao;

	/** @var \POS\Model\PaymentDao */
	public $paymentDao;

	public function __construct(UserDao $userDao, PaymentDao $paymentDao, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->paymentDao = $paymentDao;

		$users = $this->userDao->getUserNames();
		$types = array(
			PaymentDao::TYPE_BANK_ACCOUNT => "Bankovní účet"
		);

		$this->addSelect('userID', 'Uživatel', $users);
		$this->addSelect('type', 'Platba přes', $types);
		$this->addDateTimePicker('from', 'Od')
			->addRule(Form::FILLED)
			->addRule(Form::MAX_LENGTH, null, 19);
		$this->addText('countDays', 'Počet dní')
			->addRule(Form::INTEGER, "Počet dní musí být číslo");
		$this->addSubmit("submit", "Vytvořit");

		$this->onSuccess[] = callback($this, 'submitted');
	}

	public function submitted(PaymentNewForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$from = new DateTime($values->from);
		$to = new DateTime($values->from);
		$to->modify("+ $values->countDays days");

		$this->paymentDao->insert(array(
			PaymentDao::COLUMN_USER_ID => $values->userID,
			PaymentDao::COLUMN_TYPE => $values->type,
			PaymentDao::COLUMN_FROM => $from,
			PaymentDao::COLUMN_TO => $to
		));

		$presenter->flashMessage('Platba byla zadána.');
		$presenter->redirect('this');
	}

}
