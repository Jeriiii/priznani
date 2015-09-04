<?php

namespace Nette\Application\UI\Form;

use POS\Model\UserGalleryDao,
	POS\Model\UserImageDao,
	POS\Model\StreamDao;
use POS\Model\UserDao;
use NetteExt\Image;
use Nette\Application\UI\Form\UserGalleryBaseForm;
use NetteExt\Uploader\ImageUploader;

/**
 * Formulář pro nahrávání profilových fotek.
 */
class SimpleProfilePhotoUploadForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, ImageUploader $imageUploader, $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $imageUploader, $parent, $name);
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
		$this->addImageFields(1, false, false); //foto bez popisu a nazvu
		$this->addSubmit('uploadProfilPhoto', 'Nahrát');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, 'errorsToFlashMessages');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$images = $this->getArrayWithImages($values, 1);
		$isFilled = $this->isFillImage($images);
		if ($isFilled) {
			$presenter = $this->getPresenter();
			$uID = $presenter->getUser()->getId();
			$gallery = $this->userGalleryDao->findProfileGallery($uID);
			if (!$gallery) {
				$gallery = $this->userGalleryDao->createProfileGallery($uID);
			}
			$allow = $this->saveImages($images, $uID, $gallery->id, TRUE);
			$presenter->flashMessage('Profilové foto bylo uloženo.');
			$presenter->redirect('this');
		} else {
			$this->addError("Vyberte platný soubor");
		}
	}

}
