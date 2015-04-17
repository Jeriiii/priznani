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

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/** @var int Id uživatele, se kterým si chci psát */
	private $userInChatID;

	public function startup() {
		parent::startup();
		$this->checkLoggedIn();
	}

	public function actionDefault(/* uživatel se kterým si chceme psát. */ $userInChatID) {
		$this->userInChatID = $userInChatID;
	}

	public function actionMobileDefault(/* uživatel se kterým si chceme psát. */ $userInChatID) {
		$this->userInChatID = $userInChatID;
	}

	public function renderMobileDefault($userInChatID) {
		$this->template->userInChat = $this->userDao->find($userInChatID);
	}

	protected function createComponentConversation($name) {


		$messages = $this->chatMessagesDao->getLastTextMessagesBetweenUsers($this->loggedUser->id, $this->userInChatID);

		$chatStream = new ChatStream($this->chatMessagesDao, $this->loggedUser, $messages, 30, $this, $name);
		$chatStream->setUserInChatID($this->userInChatID);

		return $chatStream;
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
		$messages = $this->chatMessagesDao->getMessagesByConversation($valConversationID);

		$chatStream = new ChatStream($this->chatMessagesDao, $this->loggedUser, $messages, 30, $this, $name);
		$chatStream->setConversationID($valConversationID);

		return $chatStream;
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
