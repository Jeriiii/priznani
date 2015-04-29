<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Stream na velkých chat.
 *
 * @author Petr Kukrál
 */

namespace POSComponent\Stream;

use Nette\ComponentModel\IContainer;
use Nette\Database\Table\Selection;
use Nette\Application\UI\Form\MessageNewForm;
use POS\Model\ChatMessagesDao;

class ChatStream extends \POSComponent\BaseProjectControl implements \IStream {

	/** @var Selection Zprávy co jsou v konverzaci - při předání v konstruktoru by měli být nevyfiltrované */
	private $messages;

	/** @var int Posun (zpráv) od konce konverzace. 0 = načti poslední zprávy z konverzace */
	private $offset;

	/** @var int Maximální počet zpráv načtených na jeden požadavek */
	private $limit;

	/** @var ChatMessageDao */
	private $chatMessagesDao;

	/** @var ActiveRow|ArrayHash */
	private $loggedUser;

	/** @var int ID konverzace */
	private $conversationID = null;

	/** @var int ID uživatele, se kterým si píši */
	private $userInChatID = null;

	public function __construct(ChatMessagesDao $chatMessagesDao, $loggedUser, Selection $messages = null, $limit = 10, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->limit = $limit;
		$this->messages = $messages;
		$this->chatMessagesDao = $chatMessagesDao;
		$this->loggedUser = $loggedUser;
	}

	public function setUserInChatID($userInChatID) {
		$this->userInChatID = $userInChatID;
	}

	public function setConversationID($conversationID) {
		$this->conversationID = $conversationID;
	}

	public function render() {
		if (empty($this->userInChatID) && empty($this->conversationID)) {
			throw new Exception("You must set userInChatID or conversationID variable.");
		}

		if (!$this->getPresenter()->isAjax()) {
			$this->setData();
			$lastMessage = $this->messages->fetch(); //při obnovení stránky, počítá s tím, že messages už nejsou potřeba (převedeny do pole)
			if ($lastMessage) {
				$this->template->lastId = $lastMessage->id;
			} else {
				$this->template->lastId = 0;
			}
		}
		$this->template->loggedUser = $this->loggedUser;
		$this->template->setFile(dirname(__FILE__) . '/chatStream.latte');
		$this->template->render();
	}

	/**
	 * Tuto metodu zavolejte ze metody render. Nastavý data, který se mají vrátit v ajaxovém i normálním
	 * požadavku v závislosti na předaném offsetu (posunu od shora).
	 * @param int $offset Offset předaný metodou handleGetMoreData. Při vyrendrování komponenty je nula.
	 */
	public function handleGetMoreData($offset) {
		$this->offset = $offset;
		$this->setData($offset);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('stream-messages');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * Tuto metodu zavolejte ze metody render.
	 * Do snippetu s novými zprávami načte všechny zprávy s novějším id než je dané id.
	 * @param int $lastId dané id
	 */
	public function handleGetNewData($lastId) {
		$newMessages = $this->getNewMessages($lastId);
		$this->template->newMessages = $newMessages;
		if ($this->presenter->isAjax()) {
			$this->invalidateControl('new-stream-messages');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * Vrátí novější zprávy než je zpráva $lastMessageId
	 * @param int $lastMessageId Zpráva, od které chci další zprávy.
	 * @return Selection Zprávy.
	 */
	private function getNewMessages($lastMessageId) {
		if (isset($this->conversationID)) {
			return $this->chatMessagesDao->getNewMessagesFromConversation($this->conversationID, $lastMessageId);
		} else {
			return $this->chatMessagesDao->getAllNewerMessagesBetween($lastMessageId, $this->loggedUser->id, $this->userInChatID);
		}
	}

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function setData($offset = 0) {
		if (!empty($offset)) {
			$messages = $this->messages->limit($this->limit, $offset);
		} else {
			$messages = $this->messages->limit($this->limit);
		}

		/* spočítá počet položek ve streamu */
		$countItems = count($messages);
		$this->template->isLast = $this->limit > $countItems ? true : false;
		/* přetočení prvků, aby byla nejnovější zpráva poslední ze zpráv omezených limitem */
		$this->template->messages = $this->reverseSelection($messages);
	}

	/**
	 * Vrátí Selection v opačném pořadí ve formě pole - tj. vrací pole s prvky selection, akorát obráceně
	 */
	public function reverseSelection(Selection $selection) {
		$retArray = array();
		foreach ($selection as $selItem) {
			array_unshift($retArray, $selItem);
		}
		return $retArray;
	}

	protected function createComponentMessageNewForm($name) {
		if (isset($this->conversationID)) {
			$messageNewForm = new MessageNewForm($this->chatMessagesDao, $this->loggedUser->id, $this, $name);
			$messageNewForm->setConversationID($this->conversationID);
			return $messageNewForm;
		} else {
			$messageNewForm = new MessageNewForm($this->chatMessagesDao, $this->loggedUser->id, $this, $name);
			$messageNewForm->setRecipientID($this->userInChatID);
			return $messageNewForm;
		}
	}

}
