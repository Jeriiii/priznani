<?php

namespace Nette\Application\UI\Form;

use Nette\ComponentModel\IContainer;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;

/**
 * Vytvoří novou uživatelskou galerii.
 */
class UserGalleryNewForm extends UserGalleryBaseForm {

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
	const NUMBER_OF_IMAGE = 4;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $parent, $name);

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;

		$this->addGroup('Fotografie (' . NUMBER_OF_IMAGE . ' x 4MB)');

		$this->addImageFields(NUMBER_OF_IMAGE, FALSE, FALSE);

		$this->addGroup('Kategorie');
		$this->genderCheckboxes();

		$this->addSubmit("submit", "Vytvořit galerie")
			->setAttribute('class', 'btn-main medium');

		$this->onValidate[] = callback($this, 'checkboxValidation');

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryNewForm $form) {
		$values = $form->values;

		$images = $this->getArrayWithImages($values, NUMBER_OF_IMAGE);

		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPresenter();
			$userID = $presenter->getUser()->getId();

			unset($values->agreement);

			$galleryID = $this->saveGallery($values, $userID);
			$this->saveImages($images, $values, $userID, $galleryID);

			$presenter->flashMessage('Galerie byla vytvořena. Fotky budou nejdříve schváleny adminem.');
			$presenter->redirect('Galleries:');
		}
	}

	/**
	 * Uloží galerii do databáze
	 * @param Nette\ArrayHash $values
	 * @param int $userID
	 * @return int
	 */
	private function saveGallery($values, $userID) {
		$valuesGallery['name'] = $values->name;
		$valuesGallery['description'] = $values->descriptionGallery;
		$valuesGallery['userId'] = $userID;
		$valuesGallery['man'] = $values->man;
		$valuesGallery['women'] = $values->women;
		$valuesGallery['couple'] = $values->couple;
		$valuesGallery['more'] = $values->more;

		$gallery = $this->userGalleryDao->insert($valuesGallery);
		return $gallery->id;
	}

}
