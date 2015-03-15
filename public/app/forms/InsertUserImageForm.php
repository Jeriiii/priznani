<?php

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Form\Upload\UploadImage;
use POS\Model\UserImageDao;

/**
 * Formulář pro vložení uživatelské fotky
 */
class InsertUserForm extends ImageBaseForm {

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;
	public $galleryID;

	public function __construct($parent = NULL, $name = NULL, $galleryID, UserImageDao $userImageDao) {
		parent::__construct($parent, $name);

		$this->userImageDao = $userImageDao;
		$this->galleryID = $galleryID;

		$this->addUpload("image", "Vložte obrázek");

		$this->addSubmit('send', 'Nahrát');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(InsertUserForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$suffix = $this->suffix($values->image->getName());

		$image = $this->userImageDao->insertImage("muj obr", $suffix, "popisek", $this->galleryID);

		$galleryDir = GalleryPathCreator::getUserGalleryFolder($this->galleryID, 1);
		UploadImage::upload($values->image, $image->id, $suffix, $galleryDir, 700, 500, 150, 90);

		$presenter->flashMessage('Nahráno');
		$presenter->redirect('this');
	}

}
