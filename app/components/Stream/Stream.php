<?php

/**
 * Description of Stream
 *
 * @author Petr
 */
use Nette\Application\UI\Form as Frm;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\ConfessionDao;

/**
 * Nekonečný seznam přiznání, fotek a dalších příspěvků od uživatele včetně fomulářů.
 */
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

	/**
	 * Přidá fotky do defaultní galerie.
	 * @param type $name
	 * @return \Nette\Application\UI\Form\NewStreamImageForm
	 */
	protected function createComponentNewStreamImageForm($name) {
		return new Frm\NewStreamImageForm($this->userGalleryDao, $this->userImageDao, $this, $name);
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
