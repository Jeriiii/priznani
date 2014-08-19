<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\StatusDao;
use POS\Model\StreamDao;

class AddStatusForm extends BaseForm {

	/**
	 * @var \POS\Model\StatusDao
	 */
	public $statusDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	public function __construct(StreamDao $streamDao, StatusDao $statusDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->statusDao = $statusDao;
		$this->streamDao = $streamDao;

		$this->addTextarea("message", "")
			->addRule(Form::FILLED, "Vyplňte prosím text zprávy.")
			->addRule(Form::MAX_LENGTH, "Status je příliš dlouhý.", 600);
		$this->addSubmit("submit", "Odeslat");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$userID = $presenter->user->id;
		$values['userID'] = $userID;

		$status = $this->statusDao->insert($values);

		$this->streamDao->addNewStatus($status->id, $userID);

		$presenter->flashMessage('Status byl vložen.');
		$presenter->redirect('this');
	}

}
