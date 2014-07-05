<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseStream
 *
 * @author Mario
 */

namespace POSComponent\Stream\BaseStream;

use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;
use POS\Model\SreamDao;

class BaseStream extends \Nette\Application\UI\Control {

	protected $dataForStream;
	private $offset = null;

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 */
	public $confessionDao;

	public function __construct($data, $streamDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, ConfessionDao $confDao) {
		parent::__construct();
		$this->dataForStream = $data;
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
		$this->confessionDao = $confDao;
	}

	/**
	 * vykresli zaklad pro stream (zed s prispevky) a rozhodne se pro jednou z moznosti - globalni stream nebo uzivatelsky profil stream
	 * @param type $mode
	 * @param type $templateName
	 */
	public function renderBase($mode, $templateName = "baseStream.latte") {

		if ($mode == "mainStream") {
			$this->renderMainStream($templateName);
		}

		if ($mode == "profilStream") {
			$this->renderProfileStream($templateName);
		}

		$this->template->render();
	}

	/**
	 * vykresli globalni stream - activity stream
	 * @param type $templateName
	 */
	private function renderMainStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * vykresli uzivatelsky stream - profil
	 * @param type $templateName
	 */
	private function renderProfileStream($templateName) {
		$this->setNewOffset();

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
	}

	/**
	 * Metoda nastavuje novy offset pro nacitani dalsich prispevku uzivatele
	 */
	public function setNewOffset() {
		$offset = 4;
		if (!empty($this->offset)) {
			$this->template->stream = $this->dataForStream->limit($offset, $this->offset);
		} else {
			$this->template->stream = $this->dataForStream->limit($offset);
		}
		$this->template->offset = $offset;
	}

	/**
	 * vraci dalsi data do streamu, ktere snippet appenduje
	 * @param type $offset
	 */
	public function handleGetMoreData($offset) {
		$this->offset = $offset;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('posts');
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * vypsani vice fb komentaru
	 * @return \Nette\Application\UI\Multiplier
	 */
	protected function createComponentFbControl() {
		$streamItems = $this->dataForStream;

		$url = "url";

		return new \Nette\Application\UI\Multiplier(function ($streamItem) use ($streamItems, $url) {
			return new \FbLikeAndCom($streamItems[$streamItem], $url);
		});
	}

}
