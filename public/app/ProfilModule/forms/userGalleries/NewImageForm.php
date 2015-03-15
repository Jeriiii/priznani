<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;

/**
 * vkládá nové fotky do uživatelské galerie
 */
class NewImageForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 */
	public $streamDao;

	/**
	 * počet možných polí pro obrázky při vytvoření galerie
	 */
	const NUMBER_OF_IMAGE = 4;

	public $galleryID;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, $galleryID, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;

		$this->galleryID = $galleryID;
		$gallery = $this->userGalleryDao->find($galleryID);

		$this->addGroup('Fotografie (' . self::NUMBER_OF_IMAGE . ' x 4MB)');
		$this->addImageFields(self::NUMBER_OF_IMAGE);

		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class', 'btn-main medium');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewImageForm $form) {
		$values = $form->values;

		$images = $this->getArrayWithImages($values, self::NUMBER_OF_IMAGE);

		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {
			$presenter = $this->getPresenter();
			$uID = $presenter->getUser()->getId();

			$allow = $this->saveImages($images, $uID, $this->galleryID);

			if ($allow) {
				$presenter->flashMessage('Fotky byly přidané.');
			} else {
				$presenter->flashMessage('Fotky byly přidané. Nyní jsou ve frontě na schválení. Po schválení 1. fotky se ostatní schvalují automaticky.');
			}
			$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $this->galleryID));
		}
	}

}