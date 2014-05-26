<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stream
 *
 * @author Petr
 */
use Nette\Application\UI\Form as Frm;

class Stream extends Nette\Application\UI\Control {

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

	public function __construct($data, $streamDao, UserGalleryDao $userGalleryDao, UserImageDao $userImageDao) {
		parent::__construct();
		$this->dataForStream = $data;
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
	}

	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/stream.latte');
		// TO DO - poslání dat šabloně
		$offset = 4;
		if (!empty($this->offset)) {
			$this->template->stream = $this->dataForStream->limit($offset, $this->offset);
			$this->template->render();
		} else {
			$this->template->stream = $this->dataForStream->limit($offset);
			$this->template->render();
		}
		$this->template->offset = $offset;
	}

	/* vrací další data do streamu */

	public function handleGetMoreData($offset) {
		$this->offset = $offset;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl('posts');
		} else {
			$this->redirect('this');
		}
	}

	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this, $name);
	}

	protected function createComponentAddItemForm($name) {
		return new Frm\AddItemForm($this, $name);
	}

	protected function createComponentFilterForm($name) {
		return new Frm\FilterStreamForm($this, $name);
	}

	/* pro vypsani vice fb komentaru */

	protected function createComponentFbControl() {
		$streamItems = $this->dataForStream;

		$url = "url";

		return new Nette\Application\UI\Multiplier(function ($streamItem) use ($streamItems, $url) {
			return new FbLikeAndCom($streamItems[$streamItem], $url);
		});
	}

}

?>
