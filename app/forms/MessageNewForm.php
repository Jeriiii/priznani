<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;
use POSComponent\Comments\BaseComments;
use POS\Model\ChatMessagesDao;
use POSComponent\Stream\ChatStream;

/**
 * Přidá novou zprávu. Volá se ze streamu zpráv z vlastního presenteru.
 *
 * @author Petr Kukrál
 */
class MessageNewForm extends BaseForm {

	/** @var ChatMessagesDao */
	private $chatMessagesDao;

	/** @var ChatStream */
	private $chatStream;

	/** @var int ID konverzace */
	private $conversationID;

	/** @var int ID odesílatele zprávy */
	private $senderID;

	public function __construct(ChatMessagesDao $chatMessagesDao, $senderID, $conversationID, ChatStream $chatStream, $name = NULL) {
		parent::__construct($chatStream, $name);
		$this->chatMessagesDao = $chatMessagesDao;
		$this->chatStream = $chatStream;
		$this->conversationID = $conversationID;
		$this->senderID = $senderID;

		$this->ajax();

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

	public function submitted(MessageNewForm $form) {
		$values = $form->getValues();
		$this->chatMessagesDao->addConversationMessage(
			$this->senderID, $this->conversationID, $values->message
		);


		if ($this->presenter->isAjax()) {
			$form->clearFields();
			$this->chatStream->redrawControl('messageNewForm');
			/* nepřekreslovat nové zprávy - smaže to předchozí nové zprávy */
		} else {
			$this->presenter->redirect('this');
		}
	}

}
