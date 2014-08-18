<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;

/**
 * Komponenta celého webového chatu na POS
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class PosChat extends BaseProjectControl {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->chatManager = $manager;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/poschat.latte');
		$user = $this->getPresenter()->getUser();
		$template->logged = $user->isLoggedIn() && $user->getIdentity();
		$template->render();
	}

	/**
	 * Vytvoření komponenty zprostředkovávající seznam kontaktů
	 * @return \POSComponent\Chat\StandardContactList
	 */
	protected function createComponentContactList() {
		return new StandardContactList($this->chatManager);
	}

	/**
	 * Vytvoření komponenty s konverzacemi (poslední zprávy)
	 * @return \POSComponent\Chat\StandardConversationList
	 */
	protected function createComponentConversationList() {
		return new StandardConversationList($this->chatManager);
	}

	/**
	 * Vytvoření komponenty pro samotnou komunikaci (odesílání a příjmání zpráv)
	 * @return \POSComponent\Chat\StandardCommunicator
	 */
	protected function createComponentCommunicator() {
		return new StandardCommunicator($this->chatManager);
	}

}
