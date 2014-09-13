<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POSComponent\BaseProjectControl;
use Nette\Application\UI\Form as Frm;

/**
 * Komponenta pro vykreslení tlačítek na lajkování.
 *
 * @author Daniel Holubář
 */
class BaseComments extends BaseProjectControl {

	/**
	 * @var \POS\Model\AbstractDao
	 */
	public $dao;

	/**
	 *
	 * @var int ID ID objektu, který komentujeme
	 */
	public $ID;

	/**
	 * @const počet zobrazovaných komentářů
	 */
	const NUMBER_OF_SHOWED_COMMENTS = 2;

	public function __construct($dao, $ID) {
		parent::__construct();
		$this->dao = $dao;
		$this->ID = $ID;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/baseComments.latte');
		$template->newestComments = $this->dao->getTwoNewestComments($this->ID);
		$template->allComments = $this->dao->getAllImageComments($this->ID);
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

}
