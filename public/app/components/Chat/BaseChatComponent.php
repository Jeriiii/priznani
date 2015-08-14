<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;

/**
 * Základ pro každou komponentu chatu
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
abstract class BaseChatComponent extends BaseProjectControl {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/** Proměnná s uživatelskými daty (cachovaný řádek z tabulky users). Obsahuje relace na profilFoto, gallery, property @var ArrayHash|ActiveRow řádek z tabulky users */
	protected $loggedUser;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager, $loggedUser, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->chatManager = $manager;
		$this->loggedUser = $loggedUser;
	}

}
