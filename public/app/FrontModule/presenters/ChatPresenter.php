<?php

use POSComponent\Chat\MobileChat;
use POSComponent\Stream\ChatStream;
use POSComponent\Chat\MobileContactList;
use POSComponent\Chat\AndroidChat;
use POS\Chat\ChatManager;
use POSComponent\Chat\React\ReactFullscreenChat;
use NetteExt\DaoBox;
use UserBlock\UserBlocker;
use POSComponent\Confirm;

/**
 * Pro práci se zprávami přes celoou stránku
 */
class ChatPresenter extends BasePresenter {

	/** @var \POS\Chat\ChatManager @inject */
	public $chatManager;

	/** @var \POS\Model\UserBlockedDao @inject */
	public $blockedDao;

	/** @var \POS\Model\ChatMessagesDao @inject */
	public $chatMessagesDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var int Id uživatele, se kterým si chci psát */
	private $userInChatID;

	public function startup() {
		parent::startup();
		$this->checkLoggedIn();
	}

	public function actionDefault(/* uživatel se kterým si chceme psát. */ $userInChatID) {
		$this->userInChatID = $userInChatID;
		$this->chatMessagesDao->setAllMessagesReaded($userInChatID, $this->getUser()->id, TRUE); //nastavení zpráv jako přečtených
	}

	public function renderMobileDefault($userInChatID) {
		if (empty($userInChatID)) {
			$this->flashMessage('Nejdříve vyberte uživatele, se kterým si chcete povídat');
			$this->redirect('Chat:conversations');
		}

		$this->defaultRenderDefault($userInChatID);
	}

	public function renderDefault($userInChatID) {
		if (empty($userInChatID)) {
			$this->flashMessage('Nejdříve vyberte uživatele, se kterým si chcete povídat');
			$this->redirect(':OnePage:');
		}

		$this->defaultRenderDefault($userInChatID);
	}

	/** Metoda volaná v obou renderech (mobilní i desktopový) */
	private function defaultRenderDefault($userInChatID) {
		$this->template->userInChat = $this->userDao->find($userInChatID);
		if ($this->blockedDao->isBlocked($this->getUser()->getId(), $userInChatID)) {
			$this->template->blockedMessage = ChatManager::USER_IS_BLOCKING_MESSAGE;
		}
		if ($this->blockedDao->isBlocked($userInChatID, $this->getUser()->getId())) {
			$this->template->blockedMessage = ChatManager::USER_IS_BLOCKED_MESSAGE;
		}
	}

	protected function createComponentConversation($name) {
		return new ReactFullscreenChat($this->chatManager, $this->loggedUser, $this->userInChatID, $this, $name);
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

		$chatStream = new ChatStream($this->chatManager, $this->chatMessagesDao, $this->loggedUser, $messages, 30, $this, $name);
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

	/**
	 * Vytvoření mobilního chatu (http komunikace) pro android
	 * @param string $name jméno komponenty
	 * @return AndroidChat komponenta
	 */
	public function createComponentAndroidChat($name) {
		return new AndroidChat($this->chatManager, $this->loggedUser, $this, $name);
	}

	/**
	 * Vytvoření komponenty zprostředkovávající seznam kontaktů
	 * @return \POSComponent\Chat\MobileContactList
	 */
	protected function createComponentMobileContactList() {
		return new MobileContactList($this->chatManager, $this->loggedUser);
	}

	/**
	 * Zablokuje uživatele.
	 * @param int $blockUserID Id uživatele, který se má blokovat.
	 */
	public function handleBlockUser($blockUserID) {
		$blocker = $this->createUserBlocker(); /* zablokuje uživatele */
		$blocker->blockUser($blockUserID, $this->loggedUser, $this->session);
		$this->redirect('Profil:Show:', array('id' => $blockUserID, 'weAreSorry' => 1));
	}

	protected function createComponentBlockUser($name) {
		$blockUser = new Confirm($this, $name, TRUE, FALSE);
		$blockUser->setTittle("Blokovat uživatele");
		$blockUser->setMessage("Opravdu chcete blokovat tohoto uživatele?");
		$blockUser->setBtnText(" ");
		$blockUser->setBtnClass("block-user-confirm");
		return $blockUser;
	}

	/**
	 * Továrnička na třídu pro blokování/odblokování uživatele
	 */
	private function createUserBlocker() {
		$daoBox = new DaoBox();

		$daoBox->userDao = $this->userDao;
		$daoBox->streamDao = $this->streamDao;
		$daoBox->userCategoryDao = $this->userCategoryDao;
		$daoBox->userBlockedDao = $this->userBlockedDao;

		$blocker = new UserBlocker($daoBox);

		return $blocker;
	}

}
