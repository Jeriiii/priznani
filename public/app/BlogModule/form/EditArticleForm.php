<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings as Strings;
use POS\Model\BlogDao;

/**
 * Změní článek.
 */
class EditArticleForm extends NewArticleForm {

	/** @var int Editovaný článek. */
	private $article;

	public function __construct($article, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($article, null, $parent, $name);

		$this->article = $article;

		$this->setDefaults(array(
			BlogDao::COLUMN_NAME => $article->name,
			BlogDao::COLUMN_TEXT => $article->text,
			BlogDao::COLUMN_ACCESS_RIGHTS => $article->access_rights,
			BlogDao::COLUMN_ORDER => $article->order
		));

		unset($this['image']);
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$values->url = Strings::webalize($values->name);

		$this->article->update(array(
			"name" => $values->name,
			"text" => $values->text,
			"access_rights" => $values->accessRights,
			"order" => $values->order,
			"url" => $values->url,
			BlogDao::COLUMN_EXCERPT => $values->excerpt
		));

		$presenter->redirect("Article:", $values->url);
	}

}
