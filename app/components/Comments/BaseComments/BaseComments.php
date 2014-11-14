<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POSComponent\BaseProjectControl;
use Nette\Application\UI\Form as Frm;
use POS\Model\LikeCommentDao;
use POS\Model\ICommentDao;
use POS\Model\ILikeDao;
use Nette\Database\Table\ActiveRow;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseComments extends BaseProjectControl {

	/**
	 * @var ICommentDao
	 */
	private $commentDao;

	/**
	 *
	 * @var ActiveRow $item Objekt, který komentujeme
	 */
	private $item;

	/**
	 *
	 * @var type \POS\Model\LikeCommentDao
	 */
	private $likeCommentDao;

	/**
	 * Komentáře daného obrázku
	 * @var Nette\Database\Table\Selection
	 */
	public $comments = null;

	/**
	 * @var int počet komentářů
	 */
	public $countComments = null;

	/**
	 * @const počet zobrazovaných komentářů
	 */
	const MIN_OF_SHOWED_COMMENTS = 2;

	/**
	 * Uživatelská data.
	 * @var ArrayHash|ActiveRow
	 */
	public $userData;

	/** @var boolean TRUE = zobrazí všechny komentáře */
	private $showAllComments = FALSE;

	public function __construct(ILikeDao $likeCommentDao, ICommentDao $commentDao, $item, $userData) {
		parent::__construct();
		if (!($item instanceof ActiveRow) && !($item instanceof \Nette\ArrayHash)) {
			throw new Exception("variable user must be instance of ActiveRow or ArrayHash");
		}
		if (!($item instanceof ActiveRow) && !($item instanceof \Nette\ArrayHash)) {
			throw new Exception("variable user must be instance of ActiveRow or ArrayHash");
		}
		$this->commentDao = $commentDao;
		$this->item = $item;
		$this->likeCommentDao = $likeCommentDao;
		$this->countComments = $this->item->comments;
		$this->userData = $userData;
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
		return new Frm\CommentNewForm($this->commentDao, $this->item->id, $this, $name);
	}

	/**
	 * možnost lajknutí komentáře
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	public function createComponentLikeComment() {
		// můžu tu vybrat klidně všechny, protože oni se vyberou jen stejně ty, která chce komponenta na lajkování
		// a ne všechny příspěvky z db
		$imageComments = $this->commentDao->getAllComments($this->item->id);
		return new \Nette\Application\UI\Multiplier(function ($imageComment) use ($imageComments) {
			return new \POSComponent\BaseLikes\CommentLikes($this->likeCommentDao, $imageComments->offsetGet($imageComment), $this->presenter->user->id);
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

}
