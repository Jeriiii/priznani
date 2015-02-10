<?php

use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\ChatStream;

/**
 * Pro práci se zprávami přes celoou stránku
 */
class ChatPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\ChatMessagesDao
	 * @inject
	 */
	public $chatMessagesDao;

	/** @var Selection */
	public $messages;

	public function actionValentin() {
		$valentinConversationID = 1;
		$this->messages = $this->chatMessagesDao->getMessagesByConversation($valentinConversationID);
	}

	public function renderValentin() {

	}

	protected function createComponentValChatMessages($name) {
		$valConversationID = 1;
		return new ChatStream($this->chatMessagesDao, $this->loggedUser, $valConversationID, $this->messages, 30, $this, $name);
	}

}
