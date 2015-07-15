<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat\React;

use POS\Chat\ChatManager;
use POSComponent\Chat\BaseChatComponent;

/**
 * Komponenta chatu napsaného v Reactu.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
abstract class ReactChat extends BaseChatComponent {

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager, $loggedUser, $parent = NULL, $name = NULL) {
		parent::__construct($manager, $loggedUser, $parent, $name);
		$user = $this->getPresenter()->getUser();
		if (!$user->isLoggedIn()) {
			$this->getPresenter()->redirect(":Onepage:");
		}
	}

	/**
	 * Vytvoření komponenty pro samotnou komunikaci (odesílání a příjmání zpráv)
	 * @return \POSComponent\Chat\StandardCommunicator
	 */
	protected function createComponentCommunicator() {
		return new ReactCommunicator($this->chatManager, $this->loggedUser);
	}

}
