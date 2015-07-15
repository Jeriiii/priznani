<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat\React;

use POSComponent\Chat\StandardCommunicator;
use POS\Chat\ChatManager;

/**
 * Slouží přímo ke komunikaci mezi serverem a prohlížečem, zpracovává
 * požadavky a vrací odpovědi. Veškerá komunikace ajaxem probíhá zde.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ReactCommunicator extends StandardCommunicator {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/react.latte');
		$template->paramsPrefix = $this->uniqueId . "-";
		$template->maxMessages = ChatManager::COUNT_OF_MORE_MESSAGES;
		$template->initialMaxMessages = ChatManager::COUNT_OF_LAST_MESSAGES;
		$template->render();
	}

}
