<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;
use POSComponent\Comments\BaseComments;

/**
 * Formulář pro komentář prvku
 *
 * @author Daniel Holubář
 */
class CommentNewForm extends BaseForm {

	/**
	 * @var \POS\Model\AbstractDao
	 */
	public $dao;

	/**
	 * @var int ID Id prvku, který komentujeme
	 */
	public $ID;

	/**
	 * @var BaseComments $baseCommentComp Základní komponenta pro komentáře
	 */
	private $baseCommentComp;

	/**
	 * @var int $ownerID ID uživatele, kterýmu obrázek patří.
	 */
	private $ownerID;

	public function __construct($dao, $ID, $ownerID, BaseComments $baseCommentComp = NULL, $name = NULL) {
		parent::__construct($baseCommentComp, $name);

		$this->ajax();
		$this->ownerID = $ownerID;
		$this->baseCommentComp = $baseCommentComp;
		$this->dao = $dao;
		$this->ID = $ID;


		//$this->getElementPrototype()->addAttributes(array('class' => 'ma-pekna-trida'));
		/* formulář */

		$this->addTextArea("comment", "", 60, 4)
			->addRule(Form::FILLED, "Musíte zadat text do komentáře.");
		$this->addSubmit("submit", "PŘIDAT KOMENTÁŘ");
		$this->setBootstrapRender();

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(CommentNewForm $form) {
		$values = $form->getValues();

		$userID = $this->presenter->user->id;
		$this->dao->insertNewComment($this->ID, $userID, $values->comment, $this->ownerID);

		if ($this->presenter->isAjax()) {
			$form->clearFields();
			$this->baseCommentComp->redrawControl('commentForm');
			$this->baseCommentComp->redrawControl('list');
			$this->baseCommentComp->countComments ++;
		} else {
			$this->presenter->redirect('this');
		}
	}

}