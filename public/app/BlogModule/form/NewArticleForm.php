<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings as Strings;
use POS\Model\BlogDao;
use Nette\Database\Table\ActiveRow;

/**
 * Vytvoří nový článek.
 */
class NewArticleForm extends BaseForm {

	/** @var \JKB\Model\IS\BlogDao @inject */
	private $blogDao;

	/** @var int Editovaný článek. */
	private $article;

	public function __construct(ActiveRow $article, BlogDao $blogDao = null, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->blogDao = $blogDao;
		$this->article = $article;

		$nameField = $this->addText('name', 'Jméno stránky:');
		$nameField->setRequired('Prosím vložte jméno stránky.');

		$order = $this->addText('order', 'Pořadí stránky:', 5, 5);
		$order->setRequired('Prosím vložte pořadové číslo stránky.');

		$excerpt = $this->addTextArea('excerpt', 'Úryvek:', null, 5);
		$excerpt->setAttribute("class", "editor");

		$text = $this->addTextArea('text', 'Text stránky:', null, 30);
		$text->setAttribute("class", "editor");

		$accessRights = array(
			"all" => "všichni",
			"admin" => "pouze administrátoři"
		);
		$this->addSelect("accessRights", "Kdo může stránku zobrazit", $accessRights);

		$this->addSubmit('send', 'Odeslat');
		$this->setDefaults(array(
			"order" => ($article->order + 1)
		));

		$this->onSuccess[] = callback($this, 'submitted');

		$this->setBootstrapRender();

		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$values->url = Strings::webalize($values->name);

		$this->blogDao->insert($values);

		$presenter->redirect("Article:", $values->url);
	}

}
