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

	/** @var int Id uživatele, se kterým si chci psát */
	private $userInChatID;

	public function startup() {
		parent::startup();
		$this->checkLoggedIn();
	}

	public function actionDefault(/* uživatel se kterým si chceme psát. */ $userInChatID) {
		$this->userInChatID = $userInChatID;
	}

	protected function createComponentConversation($name) {


		$messages = $this->chatMessagesDao->getLastTextMessagesBetweenUsers($this->loggedUser->id, $this->userInChatID);

		$chatStream = new ChatStream($this->chatMessagesDao, $this->loggedUser, $messages, 30, $this, $name);
		$chatStream->setUserInChatID($this->userInChatID);

		return $chatStream;
	}

	protected function createComponentValChatMessages($name) {
		$valConversationID = 1;
		$messages = $this->chatMessagesDao->getMessagesByConversation($valConversationID);

		$chatStream = new ChatStream($this->chatMessagesDao, $this->loggedUser, $messages, 30, $this, $name);
		$chatStream->setConversationID($valConversationID);

		return $chatStream;
	}

}
