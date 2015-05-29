<?php

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POS\Chat\ChatManager;

/**
 * Formulář pro odeslání zprávy do chatu
 */
class SendMessageForm extends BaseForm {

	/**
	 * @var ChatManager
	 */
	private $chatManager;

	/**
	 *
	 * @var int Id prijemce
	 */
	private $idRecipient;

	public function __construct(ChatManager $manager, $idRecipient, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->chatManager = $manager;
		$this->idRecipient = $idRecipient;

		$this->addTextArea('text', '', 40, 20)
			->addRule(Form::FILLED, 'Nejprve vyplňte zprávu.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka zprávy je %d znaků.', 1000);

		$this->setBootstrapRender();
		$this->addSubmit('send', 'Odeslat')
			->setAttribute("class", "btn-main medium button");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SendMessageForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$result = $this->chatManager->sendTextMessage($presenter->getUser()->getId(), $this->idRecipient, $values->text);
		if ($result === ChatManager::USER_IS_BLOCKED_RETCODE) {
			$this->presenter->flashMessage(ChatManager::USER_IS_BLOCKED_MESSAGE);
			$this->presenter->redirect('this');
		}
		if ($result === ChatManager::USER_IS_BLOCKING_RETCODE) {
			$this->presenter->flashMessage(ChatManager::USER_IS_BLOCKING_MESSAGE);
			$this->presenter->redirect('this');
		}

		$presenter->flashMessage('Zpráva byla odeslána.');
		$presenter->redirect('this');
	}

}
