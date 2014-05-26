<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;

/**
 * Vkládá fotky do defaultní galerie přímo ze streamu
 */
class NewStreamImageForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;

	/**
	 * počet možných polí pro obrázky při vytvoření galerie
	 */
	const NUMBER_OF_IMAGE = 3;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;

		//form
		$this->addImageFields(NUMBER_OF_IMAGE, TRUE, FALSE);

		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class', 'submit-button');

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewStreamImageForm $form) {
		$values = $form->values;

		$images = $this->getArrayWithImages($values, NUMBER_OF_IMAGE);

		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPresenter();
			$userID = $presenter->getUser()->getId();
			$defaultGallery = $this->userGalleryDao->getDefault($userID);

			if (empty($defaultGallery)) {
				$idGallery = $this->userGalleryDao->insertDefaultGallery($userID)->id;
			} else {
				$idGallery = $defaultGallery->id;
			}

			$this->saveImages($images, $userID, $idGallery);

			$presenter->flashMessage('Fotky byly přidané.');
			$presenter->redirect('OnePage:default');
		}
	}

}
