<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POSComponent\BaseProjectControl;
use Nette\Application\UI\Form as Frm;
use POS\Model\LikeImageCommentDao;
use POS\Model\ICommentDao;
use POS\Model\ILikeDao;
use Nette\Database\Table\ActiveRow;
use Nette\ArrayHash;
use Exception;
use POSComponent\Confirm;
use Nette\Application\UI\Multiplier;
use POSComponent\BaseLikes\CommentLikes;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseComments extends BaseProjectControl {

	/** @var ICommentDao */
	private $commentDao;

	/** @var ActiveRow $item Objekt, který komentujeme */
	private $item;

	/** @var type \POS\Model\LikeImageCommentDao */
	private $likeImageCommentDao;

	/**
	 * Komentáře daného obrázku
	 * @var Nette\Database\Table\Selection
	 */
	public $comments = null;

	/** @var int počet komentářů */
	public $countComments = null;

	/** @const počet zobrazovaných komentářů */
	const MIN_OF_SHOWED_COMMENTS = 2;

	/**
	 * Uživatelská data.
	 * @var ArrayHash|ActiveRow
	 */
	public $userData;

	/** @var boolean TRUE = zobrazí všechny komentáře */
	private $showAllComments = FALSE;

	/** @var booblean Mají se vykreslit js scripty, který převytvoří confirm okénka */
	private $redrawConfirm = FALSE;

	/** @var int $ownerID ID uživatele, kterýmu obrázek patří. */
	private $ownerID;

	/** @var \POS\UserPreferences\StreamUserPreferences objekt dat, který obsahuje prvky souvisejícího streamu */
	private $cachedStreamPreferences;

	public function __construct(ILikeDao $likeImageCommentDao, ICommentDao $commentDao, $item, $userData, $ownerID, StreamUserPreferences $cachedStreamPreferences = NULL) {
		parent::__construct();
		if (!($item instanceof ActiveRow) && !($item instanceof \Nette\ArrayHash)) {
			throw new \Exception("variable $item must be instance of ActiveRow or ArrayHash");
		}
		//zakomentováno z důvodu, že zatím neumím při vytváření přes multiper dostat presenter
//		if (!($userData instanceof ActiveRow) && !($userData instanceof \Nette\ArrayHash) && $this->getPresenter()->getUser()->isLoggedIn()
		//) {
//			throw new \Exception("variable $userData must be instance of ActiveRow or ArrayHash");
//		}
		$this->commentDao = $commentDao;
		$this->item = $item;
		$this->likeImageCommentDao = $likeImageCommentDao;
		$this->countComments = $this->item->comments;
		$this->userData = $userData;
		$this->ownerID = $ownerID;
		$this->cachedStreamPreferences = $cachedStreamPreferences;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseComments.latte');
		$template->comments = $this->getComments();
		$template->countComments = $this->countComments;
		$template->minShowComments = self::MIN_OF_SHOWED_COMMENTS;
		$template->showAllComments = $this->showAllComments;
		$template->userData = $this->userData;
		$template->redrawConfirm = $this->redrawConfirm;
		$template->render();
	}

	public function handleShowAllComment() {
		$this->showAllComments = TRUE;

		$this->redrawControl();
	}

	/**
	 * Vrátí komentáře ve správném počtu (pár komentářů nebo všechny).
	 * @return Nette\Database\Table\Selection
	 */
	private function getComments() {
		if (empty($this->comments)) {
			if ($this->showAllComments) {
				$this->comments = $this->commentDao->getAllComments($this->item->id);
			} else {
				$this->comments = $this->commentDao->getFewComments($this->item->id, self::MIN_OF_SHOWED_COMMENTS);
			}
		}
		return $this->comments;
	}

	/**
	 * Formulář na vložení komentáře
	 * @param type $name
	 * @return \Nette\Application\UI\Form\CommentNewForm
	 */
	public function createComponentCommentNewForm($name) {
		return new Frm\CommentNewForm($this->commentDao, $this->item->id, $this->ownerID, $this, $name);
	}

	/**
	 * možnost lajknutí komentáře
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	public function createComponentLikeComment() {
		// můžu tu vybrat klidně všechny, protože oni se vyberou jen stejně ty, která chce komponenta na lajkování
		// a ne všechny příspěvky z db
		$imageComments = $this->commentDao->getAllComments($this->item->id);
		return new Multiplier(function ($imageComment) use ($imageComments) {
			$userID = $this->presenter->user->id;
			$imageComment = $imageComments->offsetGet($imageComment);
			return new CommentLikes($this->likeImageCommentDao, $imageComment, $userID, $imageComment->userID, $this->cachedStreamPreferences);
		});
	}

	/**
	 * Signál pro smazání komentáře
	 * @param type $commentID ID komentáře, který chceme smazat
	 */
	public function handleDeleteComment($commentID) {
		$this->commentDao->delete($commentID);

		$this->redrawControl();
	}

	protected function createComponentDeleteComment($name) {
		$deleteComment = new Confirm($this, $name, TRUE, FALSE);
		$deleteComment->setTittle("Smazat komentář");
		$deleteComment->setMessage("Opravdu chcete smazat komentář?");
		$deleteComment->setBtnText("×");
		$deleteComment->setBtnClass("delete-comment");
		$deleteComment->setConfirmBtnClass("ajax");
		return $deleteComment;
	}

	public function redrawControl($snippet = NULL, $redraw = TRUE) {
		parent::redrawControl($snippet, $redraw);
		$this->redrawConfirm = TRUE;
	}

}
