<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use NetteExt\Uploader\ImageUploader;

/**
 * Vkládá fotky do defaultní galerie přímo ze streamu
 */
class NewStreamImageForm extends UserGalleryImagesBaseForm {

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\ImageGalleryDao */
	public $userImageDao;

	/** počet možných polí pro obrázky při vytvoření galerie */
	const NUMBER_OF_IMAGE = 3;

	/**
	 * počet možných polí pro obrázky při vytvoření galerie na mobilním zařízení
	 */
	const NUMBER_OF_MOBILE_IMAGE = 2;

	/**
	 * Počet nahrávacích polí v tomto požadavku (nastaveno podle vlastností zařízení)
	 * @var int
	 */
	private $actualNumberOfImage = 0;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, ImageUploader $imageUploader, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $imageUploader, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;

		if ($this->deviceDetector->isMobile()) {
			$this->actualNumberOfImage = self::NUMBER_OF_MOBILE_IMAGE;
		} else {
			$this->actualNumberOfImage = self::NUMBER_OF_IMAGE;
		}

		//form
		$this->addImageFields($this->actualNumberOfImage, TRUE, FALSE);

		$this->setInputContainer(FALSE);
		$this->setBootstrapRender();

		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class', 'submit-button');

		if ($this->deviceDetector->isMobile()) {
			$this->onValidate[] = callback($this, 'errorsToFlashMessages');
		}
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewStreamImageForm $form) {
		$values = $form->values;

		$images = $this->getArrayWithImages($values, $this->actualNumberOfImage);

		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPresenter();
			$userID = $presenter->getUser()->getId();
			$defaultGallery = $this->userGalleryDao->findDefaultGallery($userID);

			if (empty($defaultGallery)) {
				$idGallery = $this->userGalleryDao->createDefaultGallery($userID)->id;
			} else {
				$idGallery = $defaultGallery->id;
			}

			$allow = $this->saveImages($images, $userID, $idGallery);

			if ($allow) {
				$presenter->flashMessage('Fotky byly přidané.');
			} else {
				$presenter->flashMessage('Fotky byly přidané. Nyní jsou ve frontě na schválení. Po schválení 1. fotky se ostatní schvalují automaticky..');
			}
			$presenter->redirect('OnePage:default');
		}
	}

}
