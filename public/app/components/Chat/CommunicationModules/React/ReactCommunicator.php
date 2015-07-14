<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat\React;

use POSComponent\Chat\StandardCommunicator;

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
		$template->sendMessageLink = $this->link("sendMessage!");
		$template->refreshMessagesLink = $this->link("refreshMessages!");
		$template->loadMessagesLink = $this->link("loadMessages!");
		$template->render();
	}

}
