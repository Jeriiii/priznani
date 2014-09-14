<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POSComponent\BaseProjectControl;
use Nette\Application\UI\Form as Frm;
use POS\Model\LikeCommentDao;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseComments extends BaseProjectControl {

	/**
	 * @var \POS\Model\AbstractDao
	 */
	private $dao;

	/**
	 *
	 * @var int ID ID objektu, který komentujeme
	 */
	private $ID;

	/**
	 *
	 * @var type \POS\Model\LikeCommentDao
	 */
	private $likeCommentDao;

	/**
	 * Dva komentáře daného obrázku
	 * @var Nette\Database\Table\Selection
	 */
	public $newestComments;

	/**
	 * Všechny komentáře daného obrázku
	 * @var Nette\Database\Table\Selection
	 */
	public $allComments;

	/**
	 * @const počet zobrazovaných komentářů
	 */
	const NUMBER_OF_SHOWED_COMMENTS = 2;

	public function __construct(LikeCommentDao $likeCommentDao, $dao, $ID, $newestComments, $allComments) {
		parent::__construct();
		$this->dao = $dao;
		$this->ID = $ID;
		$this->newestComments = $newestComments;
		$this->allComments = $allComments;
		$this->likeCommentDao = $likeCommentDao;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseComments.latte');
		$template->newestComments = $this->newestComments;
		$template->allComments = $this->allComments;
		$template->commentsNumber = self::NUMBER_OF_SHOWED_COMMENTS;
		$template->render();
	}

	/**
	 * Formulář na vložení komentáře
	 * @param type $name
	 * @return \Nette\Application\UI\Form\CommentNewForm
	 */
	public function createComponentCommentNewForm($name) {
		return new Frm\CommentNewForm($this->dao, $this->ID, $this, $name);
	}

	/**
	 * možnost lajknutí komentáře
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
//	public function createComponentLikeComment() {
//		$imageComments = $this->allComments;
//		return new \Nette\Application\UI\Multiplier(function ($imageComment) use ($imageComments) {
//			return new \POSComponent\BaseLikes\CommentLikes($this->likeCommentDao, $imageComments->offsetGet($imageComment), $this->presenter->user->id);
//		});
//	}
}
