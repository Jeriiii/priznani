<?php

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POS\Model\FriendRequestDao;
use POS\Model\ActivitiesDao;

/**
 * Decription
 */
class RequestMessageForm extends BaseForm {

	/** @var \POS\Model\FriendRequestDao */
	private $friendRequestDao;

	/** @var int ID žadatele */
	private $userIDFrom;

	/** @var int ID příjemce */
	private $userIDTo;

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	public function __construct(ActivitiesDao $activitiesDao, FriendRequestDao $friendRequestDao, $userIDFrom, $userIDTo, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->friendRequestDao = $friendRequestDao;
		$this->userIDFrom = $userIDFrom;
		$this->userIDTo = $userIDTo;
		$this->activitiesDao = $activitiesDao;

		$message = $this->addTextArea("message", "Zpráva pro příjemce");
		$message->addRule(Form::FILLED, "Vyplňte zprávu pro příjemce.");
		$message->addRule(Form::MAX_LENGTH, "Maximální délka zprávy je %d znaků", 200);
		$message->setDefaultValue("Chci tě poznat!");

		$this->addSubmit('send', 'Odeslat');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(RequestMessageForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$friendRequest = $this->friendRequestDao->sendRequest($this->userIDFrom, $this->userIDTo, $values->message);
		$this->activitiesDao->createFriendRequestActivity($this->userIDFrom, $this->userIDTo, $friendRequest->id);

		$presenter->flashMessage('Žádost o přátelství byla odeslána.');
		$presenter->redirect('this');
	}

}
