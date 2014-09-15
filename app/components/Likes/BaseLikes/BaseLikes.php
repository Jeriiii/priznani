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
	 * @var Nette\Database\Table\ActiveRow comment obrázku pro lajknutí
	 */
	protected $imageComment;

	/**
	 * @var bool TRUE pokud uživatel dal like, jinak FALSE.
	 */
	protected $liked;

	/**
	 * @const IMAGE_LIKE_BUTTON text lajkovacího tlačítka pro obrázky
	 */
	const IMAGE_LIKE_BUTTON = "Sexy";

	/**
	 * @const COMMON_LIKE_BUTTON obecný text lajkovacího tlačítka
	 */
	const COMMON_LIKE_BUTTON = "Líbí";

	/**
	 * @const IMAGE_LABEL text do informace o přihlášení kvůli hodnocení obrázku
	 */
	const IMAGE_LABEL = "obrázku";

	/**
	 * @const STATUS_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const STATUS_LABEL = "statusu";

	/**
	 * @const COMMENT_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const COMMENT_LABEL = "komentáře";

	/**
	 * Konstruktor komponenty, pokud používáme k tvorbě lajkování obrázků, vkládáme dao spojené s obrázky, pokud
	 * používáme k tvorbě komponenty pro lajk statusů, vkládáme dao spojené se statusy, pokud chceme lajkovat commenty obrázků,
	 * vkládáme příslušné dao.
	 * @param \POS\Model\ImageLikesDao|\POS\Model\LikeStatusDao|\POS\Model\LikeCommentDao $dao dao, které se vkládá podle potřeby
	 * lajknutí obrázku/statusu/commentu obrázku
	 * @param Nette\Database\Table\ActiveRow $image lajkovaný obrázek
	 * @param Nette\Database\Table\ActiveRow $status lajkovaný status
	 * @param Nette\Database\Table\ActiveRow $imageComment lajkovaný comment obrázku
	 * @param int $userID ID lajkující uživatele
	 * @param bool $liked informace o tom jesli už uživatel tento obrázek/status/comment obrázku lajkl, nebo ne
	 */
	public function __construct($dao, $image, $status, $imageComment, $userID, $liked) {
		parent::__construct();
		$this->userID = $userID;
		$this->dao = $dao;
		$this->liked = $liked;
		if ($image != NULL) {
			$this->image = $image;
		} else if ($status != NULL) {
			$this->status = $status;
		} else {
			$this->imageComment = $imageComment;
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
		} else if ($this->dao instanceof LikeStatusDao) {
			$template->item = $this->status;
			$template->button = self::COMMON_LIKE_BUTTON;
			$template->label = self::STATUS_LABEL;
		} else {
			$template->item = $this->imageComment;
			$template->button = self::COMMON_LIKE_BUTTON;
			$template->label = self::COMMENT_LABEL;
		}
		$template->render();
	}

}
