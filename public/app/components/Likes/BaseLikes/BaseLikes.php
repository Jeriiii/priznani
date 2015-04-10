<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POSComponent\BaseProjectControl;
use POS\Model\ImageLikesDao;
use POS\Model\LikeStatusDao;
use Nette\Application\Responses\JsonResponse;
use POS\Model\AbstractDao;
use Nette\Database\Table\ActiveRow;
use POS\Model\ILikeDao;
use Nette\ArrayHash;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseLikes extends BaseProjectControl {

	/**
	 * @var ILikeDao
	 */
	public $likeDao;

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
	 * @var ActiveRow Příspěvek u kterého se má zobrazit počet like nebo se lajknout
	 */
	protected $likeItem;

	/**
	 * @var int ID uživatele, kterýmu obrázek patří.
	 */
	public $ownerID;

	/** @var \POS\UserPreferences\StreamUserPreferences objekt dat, který obsahuje prvky souvisejícího streamu */
	protected $cachedStreamPreferences;

	/**
	 * @var bool TRUE pokud právě lajknul příspěvek. Používá se k tomu
	 * že $item, třeba obrázek se pošle v konstruktoru a má konečné
	 * hodnoty třeba poč. like 5. Pak se teprve spustí signál na like,
	 * který zvýší v tabulce poč. like na 6, ale $item už je dávno uložený
	 * a má pořád počet liků 5. Po reloadu stránky si již načte správný počet
	 * liků ze změněné tabulky. Takže chyba nastává jen tehdy, pokud
	 * právě likneme příspěvek. A na to slouží $justLike. Když je na TRUE
	 * tak to znamená, že má přičíst k aktuálnímu stavu jedna. Zároveň
	 * se nastavuje na TRUE pouze tehdy, když je spuštěn signál na
	 * lajkování.
	 */
	protected $justLike = FALSE;

	/**
	 * @var string Název tlačítka na lajkování.
	 */
	protected $nameLikeButton = "Líbí";

	/**
	 * @var string Název lajkovací fce k doplnění věty: K ohodnocení $nameLabel se musíte přihlásit
	 */
	protected $nameLabel = null;

	const DEFAULT_NAME_LIKE_BUTTON = "Líbí";

	/**
	 * @const COMMON_LIKE_BUTTON obecný text lajkovacího tlačítka
	 */
	const COMMON_LIKE_BUTTON = "Líbí";

	/**
	 * @const COMMENT_LABEL text do informace o přihlášení kvůli hodnocení statusu
	 */
	const COMMENT_LABEL = "komentáře";

	/**
	 * Konstruktor komponenty, pokud používáme k tvorbě lajkování obrázků, vkládáme dao spojené s obrázky, pokud
	 * používáme k tvorbě komponenty pro lajk statusů, vkládáme dao spojené se statusy, pokud chceme lajkovat commenty obrázků,
	 * vkládáme příslušné dao atd.
	 * @param \POS\Model\ImageLikesDao|\POS\Model\LikeStatusDao|\POS\Model\LikeImageCommentDao $likeDao dao, které se vkládá podle potřeby
	 * lajknutí obrázku/statusu/commentu obrázku
	 * @param Nette\Database\Table\ActiveRow | Nette\ArrayHash $likeItem Příspěvek u kterého se má zobrazit počet like nebo se lajknout
	 * @param int $userID ID lajkující uživatele
	 * @param int $ownerID ID uživatele, kterýmu obrázek patří.
	 * @param string $nameLabel viz. kom. třídy
	 * @param string $nameLikeButton viz. kom. třídy
	 * @param \POS\UserPreferences\StreamUserPreferences $cachedStreamPreferences objekt obsahující položky ve streamu, pokud se používá cachování. Pokud se nepoužívá, pak je NULL
	 */
	public function __construct(ILikeDao $likeDao, $likeItem, $userID, $ownerID, $nameLabel, $nameLikeButton = self::DEFAULT_NAME_LIKE_BUTTON, StreamUserPreferences $cachedStreamPreferences = NULL) {
		parent::__construct();
		if (!($likeItem instanceof ActiveRow) && !($likeItem instanceof ArrayHash)) {
			throw new \Exception('Variable $likeItem must be instance of ActiveRow or ArrayHash ' . $ownerID);
		}
		$this->ownerID = $ownerID;
		$this->likeDao = $likeDao;
		$this->liked = $this->getLikedByUser($userID, $likeItem->id);
		$this->userID = $userID;
		$this->likeItem = $likeItem;
		$this->nameLabel = $nameLabel;
		$this->nameLikeButton = $nameLikeButton;
		$this->cachedStreamPreferences = $cachedStreamPreferences;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		if ($this->getDeviceDetector()->isMobile()) {
			$template->setFile(dirname(__FILE__) . '/baseLikesMobile.latte');
		} else {
			$template->setFile(dirname(__FILE__) . '/baseLikes.latte');
		}
		$template->liked = $this->liked;
		$template->justLike = $this->justLike;
		$template->button = $this->nameLikeButton;
		$template->label = $this->nameLabel;
		/* pokud uživatel právě liknul, přičteme +1. Viz komentář u proměnné $justLike */
		$template->likes = $this->justLike ? $this->likeItem->likes + 1 : $this->likeItem->likes;
		$template->item = $this->likeItem;
		$template->render();
	}

	/**
	 * Přenačte prvek ve streamu.
	 * @param int $itemID
	 */
	protected function reloadItem($itemID) {
		//if ($this->cachedStreamPreferences) {
		$this->cachedStreamPreferences->reloadItem($itemID);
		//}
	}

	/**
	 * Vrátí informaci, zda uživatel již dal like
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $commentID ID commentu, který hledáme
	 * @return bool
	 */
	protected function getLikedByUser($userID, $commentID) {
		$liked = $this->likeDao->likedByUser($userID, $commentID);
		return $liked;
	}

}
