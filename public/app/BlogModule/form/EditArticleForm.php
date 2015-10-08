<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings as Strings;
use POS\Model\BlogDao;
use NetteExt\DaoBox;

/**
 * Změní článek.
 */
class EditArticleForm extends NewArticleForm {

	/** @var int Editovaný článek. */
	private $article;

	public function __construct(DaoBox $daoBox, $article, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($article, $daoBox, $parent, $name);

		$this->article = $article;

		$this->setDefaults(array(
			BlogDao::COLUMN_NAME => $article->name,
			BlogDao::COLUMN_TEXT => $article->text,
			BlogDao::COLUMN_EXCERPT => $article->excerpt,
			BlogDao::COLUMN_ORDER => $article->order,
			BlogDao::COLUMN_RELEASE => $article->release,
		));
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$values->url = Strings::webalize($values->name);

		$image = $values->image;
		unset($values['image']);

		$this->article->update(array(
			"name" => $values->name,
			"text" => $values->text,
			"order" => $values->order,
			"url" => $values->url,
			BlogDao::COLUMN_EXCERPT => $values->excerpt
		));

		$this->uploadImage($image, $this->article);

		$presenter->redirect("Article:article", $values->url);
	}

}
