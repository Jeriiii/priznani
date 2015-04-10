<?php

use POSComponent\Chat\MobileChat;
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

	public function actionValentyn() {
		$valentinConversationID = 1;
		$this->messages = $this->chatMessagesDao->getMessagesByConversation($valentinConversationID);
	}

	public function renderValentyn() {

	}

	public function renderMobileConversations() {
		$this->template->logged = $this->getUser()->isLoggedIn();
	}

	/**
	 * Vytvoření komponenty valentýnského chatu
	 * @param string $name jméno komponenty
	 * @return ChatStream komponenta
	 */
	protected function createComponentValChatMessages($name) {
		$valConversationID = 1;
		return new ChatStream($this->chatMessagesDao, $this->loggedUser, $valConversationID, $this->messages, 30, $this, $name);
	}

	/**
	 * Vytvoření mobilního chatu
	 * @param string $name jméno komponenty
	 * @return MobileChat komponenta
	 */
	public function createComponentMobileChat($name) {
		return new MobileChat($this->chatManager, $this->loggedUser, $this, $name);
	}

}
