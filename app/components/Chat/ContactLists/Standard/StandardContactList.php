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
 * Reprezentuje seznam kontaktů
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardContactList extends BaseProjectControl implements IContactList {

	/**
	 * chat manager
	 * @var ChatManager
	 */
	protected $chatManager;

	/**
	 * Standardni konstruktor, predani sluzby chat manageru
	 */
	function __construct(ChatManager $manager) {
		$this->chatManager = $manager;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$template->render();
	}

}
