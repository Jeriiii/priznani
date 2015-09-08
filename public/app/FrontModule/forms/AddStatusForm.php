<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use NetteExt\DaoBox;
use POS\Model\ActivitiesDao;

class AddStatusForm extends BaseForm {

	/** @var \POS\Model\StatusDao */
	public $statusDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	/** @var ActiveRow|ArrayHash */
	private $userProperty;

	public function __construct(DaoBox $daoBox, $userProperty, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->statusDao = $daoBox->statusDao;
		$this->streamDao = $daoBox->streamDao;
		$this->activitiesDao = $daoBox->activitiesDao;

		$this->userProperty = $userProperty;

		$this->addTextarea("message", "")
			->addRule(Form::FILLED, "Vyplňte prosím text zprávy.")
			->addRule(Form::MAX_LENGTH, "Status je příliš dlouhý.", 600);
		$this->addSubmit("submit", "Odeslat");

		if ($this->deviceDetector->isMobile()) {
			$this->onValidate[] = callback($this, 'errorsToFlashMessages');
		}
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$userID = $presenter->user->id;
		$values['userID'] = $userID;

		$status = $this->statusDao->insert($values);

		$this->streamDao->addNewStatus($status->id, $userID, $this->userProperty->preferencesID);
		$this->activitiesDao->createStatusActivity($userID, NULL, $status->id, ActivitiesDao::TYPE_ADD_NEW);

		$presenter->flashMessage('Status byl vložen.');
		$presenter->redirect('this');
	}

}
