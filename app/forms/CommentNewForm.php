<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;

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

	public function __construct($dao, $ID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->dao = $dao;
		$this->ID = $ID;

		/* formulář */
		$this->addText("comment", "", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat text do komentáře.");

		$this->addSubmit("submit", "Vložit");
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(CommentNewForm $form) {
		$values = $form->getValues();

		$this->dao->insertNewComment($this->ID, $values->comment);

		$this->presenter->redirect('this');
	}

}
