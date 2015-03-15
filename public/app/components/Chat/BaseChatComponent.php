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
abstract class BaseChatComponent extends BaseProjectControl implements IContactList {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager) {
		parent::__construct();
		$this->chatManager = $manager;
	}

}
