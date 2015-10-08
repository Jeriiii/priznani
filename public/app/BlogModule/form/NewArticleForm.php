<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings as Strings;
use POS\Model\BlogDao;
use Nette\Database\Table\ActiveRow;
use NetteExt\File;
use NetteExt\Path\BlogImagePathCreator;
use POS\Model\BlogImageDao;
use NetteExt\DaoBox;
use Nette\Http\FileUpload;
use Nette\Image;

/**
 * Vytvoří nový článek.
 */
class NewArticleForm extends BaseForm {

	/** @var \JKB\Model\IS\BlogDao */
	private $blogDao;

	/** @var \POS\Model\BlogImageDao */
	public $blogImageDao;

	/** @var int Editovaný článek. */
	private $article;

	public function __construct(ActiveRow $article, DaoBox $daoBox, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->blogDao = $daoBox->blogDao;
		$this->blogImageDao = $daoBox->blogImageDao;

		$this->article = $article;


		$nameField = $this->addText('name', 'Jméno stránky:');
		$nameField->setRequired('Prosím vložte jméno stránky.');

		$order = $this->addText('order', 'Pořadí stránky:', 5, 5);
		$order->setRequired('Prosím vložte pořadové číslo stránky.');

		$excerpt = $this->addTextArea('excerpt', 'Úryvek:', null, 5);
		$excerpt->setAttribute("class", "editor");

		$text = $this->addTextArea('text', 'Text stránky:', null, 30);
		$text->setAttribute("class", "editor");

		$image = $this->addUpload('image', 'Obrázek článku:');

		$release = array(
			0 => "teď nevydávat",
			1 => "vydat článek okamžitě"
		);
		$this->addSelect("release", "Vydání článku", $release);

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

		$image = $values->image;
		unset($values['image']);

		$article = $this->blogDao->insert($values);

		$this->uploadImage($image, $article);

		$presenter->redirect("Article:article", $values->url);
	}

	/**
	 * Nahraje obrázek.
	 * @param FileUpload $image Obrázek, co se má nahrát.
	 * @param ActiveRow $article Článek do kterého se mají nahrát obrázky.
	 */
	protected function uploadImage(FileUpload $image, ActiveRow $article) {
		if ($image->isOk() && $image->isImage()) {
			File::createDir(BlogImagePathCreator::getArticleFolderPath($article->id)); //vytvoří složku pro obrázky tohoto článku

			$suffix = pathinfo($image->getName(), PATHINFO_EXTENSION);
			$imageDB = $this->blogImageDao->insert(array(
				BlogImageDao::COLUMN_SUFFIX => $suffix,
				BlogImageDao::COLUMN_ARTICLE_ID => $article->id,
			));

			$imgUrl = BlogImagePathCreator::getImgPath($article->id, $imageDB->id, $suffix);

			$image->move($imgUrl);

			$image = Image::fromFile($imgUrl);
			$image->resize(1024, NULL); //změna velikosti obrázku
			$image->save($imgUrl);
		}
	}

}
