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

	protected function createComponentChatMessages($name) {
		return new ChatStream($this->messages, $this->chatMessagesDao, 30, $this, $name);
	}

}
