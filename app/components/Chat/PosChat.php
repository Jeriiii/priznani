<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;

/**
 * Description of PosChat
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class PosChat extends BaseProjectControl implements IChat {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->chatManager = $manager;
	}

	/**
	 * Pošle zprávu daného uživatele jinému uživateli
	 * @param int $idSender odesilatel
	 * @param int $idRecipient prijemce
	 * @param Strin $text text zpravy
	 */
	public function sendTextMessage($idSender, $idRecipient, $text) {

	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/poschat.latte');
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
	 * Vytvoření komponenty pro samotnou komunikaci (odesílání a příjmání zpráv)
	 * @return \POSComponent\Chat\StandardCommunicator
	 */
	protected function createComponentCommunicator() {
		return new StandardCommunicator($this->chatManager);
	}

}
