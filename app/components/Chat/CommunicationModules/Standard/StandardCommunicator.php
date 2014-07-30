<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace POSComponent\Chat;

use POS\Chat\ChatManager;
use POSComponent\BaseProjectControl;
use \Nette\Utils\Json;
use Nette\Application\Responses\JsonResponse;

/**
 * Slouží přímo ke komunikaci mezi uživateli
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class StandardCommunicator extends BaseProjectControl implements ICommunicator {

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

	function handleSendMessage() {

		$json = file_get_contents("php://input");
//		$json = $this->getPresenter()->getRequest()->getPost();
		$data = Json::decode($json);
		$this->getPresenter()->sendJson($data);
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/standard.latte');
		$template->sendMessageLink = $this->link("sendMessage!");
		$template->render();
	}

}
