<?php

namespace Nette\Application\UI\Form;

use POS\Model\UserGalleryDao,
	POS\Model\UserImageDao,
	POS\Model\StreamDao;
use POS\Model\UserDao;
use NetteExt\Form\Upload\UploadImage;
use Nette\Application\UI\Form;

/**
 * Formulář pro nahrávání profilových fotek.
 */
class ProfilePhotoUploadForm extends UserGalleryImagesBaseForm {

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao */
	public $userImageDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;

		$this->addText('imageName')->setAttribute('class', 'imgCropInput');
		$imageX1 = $this->addText('imageX1')->setAttribute('class', 'imgCropInput');
		$imageX2 = $this->addText('imageX2')->setAttribute('class', 'imgCropInput');
		$imageY1 = $this->addText('imageY1')->setAttribute('class', 'imgCropInput');
		$imageY2 = $this->addText('imageY2')->setAttribute('class', 'imgCropInput');


		$this->addSubmit('uploadProfilPhoto', 'Nahrát');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$filename = $values->imageName;
		$presenter = $this->getPresenter();

		$image = UploadImage::getImageFromTemp($filename);

		if (empty($image)) {
			$presenter->flashMessage('Obrázek nenalezen, zkuste to prosím znovu.');
			$presenter->redirect('this');
		}
		$image->crop($values->imageX1, $values->imageY1, $values->imageX2 - $values->imageX1, $values->imageY2 - $values->imageY1);


		$images = array(
			array(self::IMAGE_FILE => $image,
				self::IMAGE_NAME => $filename
			)
		);

		$uID = $presenter->getUser()->getId();
		$gallery = $this->userGalleryDao->findProfileGallery($uID);
		if (!$gallery) {
			$gallery = $this->userGalleryDao->createProfileGallery($uID);
		}
		$allow = $this->saveImages($images, $uID, $gallery->id, TRUE);

		$presenter->calculateLoggedUser();

		$presenter->flashMessage('Profilové foto bylo uloženo.');

		$presenter->redirect('this');
	}

}
