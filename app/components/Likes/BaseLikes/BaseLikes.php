<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POSComponent\BaseProjectControl;
use POS\Model\ImageLikesDao;
use POS\Model\LikeStatusDao;
use Nette\Application\Responses\JsonResponse;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseLikes extends BaseProjectControl {

	/**
	 * @var \POS\Model\ImageLikesDao|\POS\Model\LikeStatusDao
	 */
	public $dao;

	/**
	 * @var int ID lajkujícího uživatele
	 */
	protected $userID;

	/**
	 * @var Nette\Database\Table\ActiveRow Obrázek pro lajknutí
	 */
	protected $image;

	/**
	 * @var Nette\Database\Table\ActiveRow Status pro lajknutí
	 */
	protected $status;

	/**
	 * @var bool TRUE pokud uživatel dal like, jinak FALSE.
	 */
	protected $liked;

	/**
	 * @const IMAGE_LIKE_BUTTON text lajkovacího tlačítka pro obrázky
	 */
	const IMAGE_LIKE_BUTTON = "Sexy";

	/**
	 * @const STATUS_LIKE_BUTTON text lajkovacího tlačítka pro statusy
	 */
	const STATUS_LIKE_BUTTON = "Líbí";

	/**
	 * @const IMAGE_LABEL text do informace o přihlášení kvůli hodnocení obrázku
	 */
	const IMAGE_LABEL = "obrázku";

	/**
	 * @const STATUS_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const STATUS_LABEL = "statusu";

	/**
	 * Konstruktor komponenty, pokud používáme k tvorbě lajkování obrázků, vkládáme dao spojené s obrázky, pokud
	 * používáme k tvorbě komponenty pro lajk statusů, vkládáme dao spojené se statusy
	 * @param \POS\Model\ImageLikesDao|\POS\Model\LikeStatusDao $dao dao, které se vkládá podle potřeby
	 * lajknutí obrázku/statusu
	 * @param Nette\Database\Table\ActiveRow $image lajkovaný obrázek
	 * @param Nette\Database\Table\ActiveRow $status lajkovaný status
	 * @param int $userID ID lajkující uživatele
	 * @param bool $liked informace o tom jesli už uživatel tento obrázek/status lajkl, nebo ne
	 */
	public function __construct($dao, $image, $status, $userID, $liked) {
		parent::__construct();
		$this->userID = $userID;
		$this->dao = $dao;
		$this->liked = $liked;
		if ($image != NULL) {
			$this->image = $image;
		} else {
			$this->status = $status;
		}
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseLikes.latte');
		$template->liked = $this->liked;
		/* naplnění daty podle toho, jestli pracujeme s obrázky nebo statusy */
		if ($this->dao instanceof ImageLikesDao) {
			$template->item = $this->image;
			$template->button = self::IMAGE_LIKE_BUTTON;
			$template->label = self::IMAGE_LABEL;
		} else {
			$template->item = $this->status;
			$template->button = self::STATUS_LIKE_BUTTON;
			$template->label = self::STATUS_LABEL;
		}
		$template->render();
	}

}
