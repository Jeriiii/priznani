<?php

use POSComponent\Chat\MobileChat;
use POSComponent\Stream\ChatStream;
use POSComponent\Chat\MobileContactList;
use POSComponent\Chat\AndroidChat;
use POS\Chat\ChatManager;
use POSComponent\Chat\React\ReactFullscreenChat;
use POS\Chat\ChatCoder;
use NetteExt\Helper\GetImgPathHelper;
use POSComponent\Chat\StandardCommunicator;

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
		$this->defaultRenderDefault($userInChatID);
	}

	public function renderDefault($userInChatID) {
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
		$this->template->userInChatCodedId = ChatCoder::encode($userInChatID);
		$this->template->loggedUser = $this->loggedUser;

		$getImagePathHelper = new GetImgPathHelper($this->context->httpRequest->url);
		$session = $this->getSession(StandardCommunicator::URL_SESSION_NAME);
		$this->template->loggedUserProfilePhotoUrl = $this->chatManager->getProfilePhotoUrl($this->loggedUser->id, $session, $getImagePathHelper);
		$this->template->loggedUserHref = $this->link(':Profil:Show:', array('id' => $this->loggedUser->id));
		$this->template->production = $this->productionMode;
	}

	protected function createComponentConversation($name) {
		return new ReactFullscreenChat($this->chatManager, $this->loggedUser, $this, $name);
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

}
