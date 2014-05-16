<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image,
	Nette\Utils\Strings;

class UserGalleryNewForm extends UserGalleryBaseForm {

	public $id_gallery;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->addGroup('Fotografie (4 x 4MB)');

		$this->addImagesFile(4, FALSE, FALSE);

		$this->addGroup('Kategorie');
		$this->addCheckbox('man', 'jen muži');

		$this->addCheckbox('women', 'jen ženy');

		$this->addCheckbox('couple', 'pár');

		$this->addCheckbox('more', '3 a více');

		$this->addSubmit("submit", "Vytvořit galerie")
			->setAttribute('class', 'btn-main medium');

		$this->onValidate[] = callback($this, 'checkboxValidation');

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryNewForm $form) {
		$values = $form->values;
		$num = $this->getNumberOfPhotos($values);

		$arr = $this->getArrayWithPhotos($values, $num);

		$isOK = $this->getOkUploadedPhotos($arr);

		if ($isOK == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPres();
			$uID = $presenter->getUser()->getId();

			//vytvoření galerie
			$idGallery = $presenter->context->createUsersGalleries()
				->insert(array(
				"name" => $values->name,
				"description" => $values->descriptionGallery,
				"userID" => $uID,
				"man" => $values->man,
				"women" => $values->women,
				"couple" => $values->couple,
				"more" => $values->more
			));

			$this->addImages($arr, $values, $uID, $idGallery);
			unset($values->agreement);

			$presenter->flashMessage('Galerie byla vytvořena. Fotky budou nejdříve schváleny adminem.');
			$presenter->redirect('Galleries:');
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}

}
