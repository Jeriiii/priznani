<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use POS\Model\ChatMessagesDao;
use POSComponent\Stream\ChatStream;
use POS\Chat\ChatManager;

/**
 * Přidá novou zprávu. Volá se ze streamu zpráv z vlastního presenteru.
 *
 * @author Petr Kukrál
 */
class MessageNewForm extends BaseForm {

	/** @var ChatStream */
	private $chatStream;

	/** @var ChatManager */
	private $chatManager;

	/** @var int ID konverzace */
	private $conversationID = null;

	/** @var int ID uživatele, se kterým si píši */
	private $recipientID = null;

	/** @var int ID odesílatele zprávy */
	private $senderID;

	public function __construct(ChatManager $manager, $senderID, ChatStream $chatStream, $name = NULL) {
		parent::__construct($chatStream, $name);
		$this->chatStream = $chatStream;
		$this->senderID = $senderID;
		$this->chatManager = $manager;

		$this->getElementPrototype()->addClass('send-msg-form');
		//$this->ajax(); - ajax zajištěn ručně ve scriptu

		/* formulář */
		$this->addText("message", "", 400, 400)
			->addRule(Form::FILLED, "Musíte zadat zprávu.")
			->addRule(Form::MAX_LENGTH, "Zpráva nesmí obsahovat více než 400 znaků.", 400)
			->setAttribute("autofocus");
		$this->addSubmit("submit", "ODESLAT");
		$this->setBootstrapRender();

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function setRecipientID($recipientID) {
		$this->recipientID = $recipientID;
	}

	public function setConversationID($conversationID) {
		$this->conversationID = $conversationID;
	}

	public function submitted(MessageNewForm $form) {
		$values = $form->getValues();

		$this->sendTextMsg($values->message);

		if ($this->presenter->isAjax()) {
			$form->clearFields();
			$this->chatStream->redrawControl('messageNewForm');
			/* nepřekreslovat nové zprávy - smaže to předchozí nové zprávy */
		} else {
			$this->presenter->redirect('this');
		}
	}

	/**
	 * Pošle textovou zprávu uživateli.
	 * @param string $message Zpráva, co se má poslat.
	 */
	private function sendTextMsg($message) {
		if (isset($this->conversationID)) {
			$this->chatManager->addConversationMessage(
				$this->senderID, $this->conversationID, $message
			);
		} else {
			$result = $this->chatManager->sendTextMessage(
				$this->senderID, $this->recipientID, $message
			);
			if ($result === ChatManager::USER_IS_BLOCKED_RETCODE) {
				$this->presenter->flashMessage(ChatManager::USER_IS_BLOCKED_MESSAGE);
				$this->presenter->redirect('this');
			}
			if ($result === ChatManager::USER_IS_BLOCKING_RETCODE) {
				$this->presenter->flashMessage(ChatManager::USER_IS_BLOCKING_MESSAGE);
				$this->presenter->redirect('this');
			}
		}
	}

}
