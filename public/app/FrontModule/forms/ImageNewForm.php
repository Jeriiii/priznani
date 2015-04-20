<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\Utils\Html;
use Nette\ComponentModel\IContainer;
use NetteExt\Image;
use POS\Model\ImageDao;
use NetteExt\Path\GalleryPathCreator;

class ImageNewForm extends ImageBaseForm {

	public $id_gallery;

	/**
	 * @var \POS\Model\ImageDao
	 * @inject
	 */
	public $imageDao;

	public function __construct(ImageDao $imageDao, $galleryID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//form
		$this->imageDao = $imageDao;
		$this->id_gallery = $galleryID;

		$this->addText("name", "Jméno obrázku:", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat jméno obrázku.");
		$this->addTextArea("comment", "Komentář obrázku", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);
		$this->addText("user_name", "Jméno  (nezveřejníme):", 30, 50)
			->addRule(Form::FILLED, "Musíte zadat jméno výherce.");
		/* $this->addText("user_phone", "Telefon výherce (nezveřejníme):", 30, 50)
		  ->addRule(Form::MIN_LENGTH, "Minimální délka tel. čísla je %d znaků", 9)
		  ->addRule(Form::FILLED, "Musíte zadat telefon výherce.");
		  $this->addText("user_email", "Email výherce (nezveřejníme):", 30, 200)
		  ->addRule(Form::EMAIL, "Email musít správný formát.")
		  ->addRule(Form::FILLED, "Musíte zadat telefon výherce."); */
		$this->addUpload("image", "Obrázek")
			->addRule(Form::IMAGE, "Obrázek musí být ve formátu gif, jpg nebo png")
			->addRule(Form::FILLED, "Musíte vybrat soubor");
		$this->addCheckbox("agreement", Html::el('a')
				->href("http://priznanizparby.cz/soutez/fotografie.pdf")
				->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");
		$this->addSubmit("submit", "Nahrát fotku");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(ImageNewForm $form) {
		$values = $form->values;
		$image = $values->image;
		$presenter = $this->getPresenter();

		unset($values->image);
		unset($values->agreement);
		$values['suffix'] = $this->suffix($image->getName());
		$values['galleryID'] = $this->id_gallery;
		$values['userID'] = 1; //$presenter->getUser()->getId();
		$values["user_phone"] = "nic";
		$values["user_email"] = "nic";

		$id = $this->imageDao->insert($values);

		$galleryFolder = GalleryPathCreator::getGalleryFolder($this->id_gallery);
		$path = $this->upload($image, $id, $values['suffix'], $galleryFolder, 500, 700, 100, 130);

		if ($path) {
			$image = Image::fromFile($path);
			$this->imageDao->update($id, array(
				ImageDao::COLUMN_GAL_SCRN_HEIGHT => $image->getHeight(),
				ImageDao::COLUMN_GAL_SCRN_WIDTH => $image->getWidth()
			));
		}

		$presenter->flashMessage('Obrázek byl vytvořen. Počkejte prosím na schválení adminem.');
		$presenter->redirect('Competition:', array("imageID" => $id));
	}

}
