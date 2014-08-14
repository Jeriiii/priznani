<?php

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POS\Model\FriendRequestDao;

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

	public function __construct(FriendRequestDao $friendRequestDao, $userIDFrom, $userIDTo, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->friendRequestDao = $friendRequestDao;
		$this->userIDFrom = $userIDFrom;
		$this->userIDTo = $userIDTo;

		$message = $this->addTextArea("message", "Zpráva pro příjemce");
		$message->addRule(Form::FILLED, "Vyplňte zprávu pro příjemce.");
		$message->addRule(Form::MAX_LENGTH, "Maximální délka zprávy je %d znaků", 200);

		$this->addSubmit('send', 'Odeslat');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(RequestMessageForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->friendRequestDao->sendRequest($this->userIDFrom, $this->userIDTo, $values->message);

		$presenter->flashMessage('Žádost o přátelství byla odeslána.');
		$presenter->redirect('this');
	}

}
