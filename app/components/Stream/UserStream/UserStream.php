<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserStream
 *
 * @author Mario
 */

namespace POSComponent\Stream\UserStream;

use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\BaseStream\BaseStream;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;

class UserStream extends BaseStream {

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	public function __construct($data, $streamDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, ConfessionDao $confDao) {
		parent::__construct($data, $streamDao, $userGalleryDao, $userImageDao, $confDao);
		$this->streamDao = $streamDao;
	}

	//put your code here
	private $offset = null;

	public function render() {
		$mode = 'mainStream';
		$templateName = "../UserStream/userStream.latte";

		$this->renderBase($mode, $templateName);
	}

	public function handleGetMoreData($offset) {
		parent::handleGetMoreData($offset);
	}

	/**
	 * Přidá fotky do defaultní galerie.
	 * @param type $name
	 * @return \Nette\Application\UI\Form\NewStreamImageForm
	 */
	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this, $name);
	}

	/**
	 * Přidání přiznání do streamu
	 * @param string $name
	 * @return \Nette\Application\UI\Form\AddItemForm
	 */
	protected function createComponentAddConfessionForm($name) {
		$addItem = new Frm\AddItemForm($this, $name);
		$addItem->setConfession($this->confessionDao);
		return $addItem;
	}

	protected function createComponentFilterForm($name) {
		return new Frm\FilterStreamForm($this, $name);
	}

}
