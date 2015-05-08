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

	const MINIMUM_CROP_SIZE = 20;

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
		$imageX1 = $this->addText('imageX1')->setAttribute('class', 'imgCropInput')
			->addRule(Form::INTEGER, 'Souřadnice musí být číselná.');
		$imageX2 = $this->addText('imageX2')->setAttribute('class', 'imgCropInput')
			->addRule(Form::INTEGER, 'Souřadnice musí být číselná.');
		$imageY1 = $this->addText('imageY1')->setAttribute('class', 'imgCropInput')
			->addRule(Form::INTEGER, 'Souřadnice musí být číselná.');
		$imageY2 = $this->addText('imageY2')->setAttribute('class', 'imgCropInput')
			->addRule(Form::INTEGER, 'Souřadnice musí být číselná.');


		$this->addSubmit('uploadProfilPhoto', 'Nahrát');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$filename = $values->imageName;
		$presenter = $this->getPresenter();

		if (!$this->isValuesOk($values)) {
			$presenter->flashMessage('Špatné souřadnice výřezu obrázku. Zkontrolujte prosím, že máte zapnutý javascript.', 'danger');
			$presenter->redirect('this');
		}
		$minSize = self::MINIMUM_CROP_SIZE;
		if (($values->imageX2 - $values->imageX1) < $minSize ||
			($values->imageY2 - $values->imageY1) < $minSize) {/* výřez nemá minimální velikost */
			$presenter->flashMessage("Výřez je příliš malý. Minimální velikost je $minSize px x $minSize px. Ořízněte prosím větší obrázek.", 'danger');
			$presenter->redirect('this');
		}

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

	/**
	 * Zkontroluje hodnoty ve formuláři
	 * @param array $values hodnoty formuláře
	 * @return bool true pokud je vše v pořádku, false pokud ne
	 */
	public function isValuesOk($values) {
		return !(empty($values->imageX1) || empty($values->imageX2) || empty($values->imageY1) || empty($values->imageY2));
	}

}
