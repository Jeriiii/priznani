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

		$this->addSubmit("submit", "Vytvořit galerie")
			->setAttribute('class', 'btn-main medium');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(UserGalleryNewForm $form) {           
		$values = $form->values;
                $num = $this->getNumberOfPhotos($values);

                $arr = $this->getArrayWithPhotos($values, $num);

                $isOK = $this->getOkUploadedPhotos($arr);

//		if ($image->error != 0 && $image2->error != 0 && $image3->error != 0 && $image4->error != 0) {
//			$this->addError("Musíte vybrat alespoň 1 soubor");
      		if($isOK == FALSE) {
		$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPres();
			$uID = $presenter->getUser()->getId();

			//$arr = array($image, $image2, $image3, $image4);                        

			//vytvoření galerie
			$valuesGallery['name'] = $values->name;
			$valuesGallery['description'] = $values->descriptionGallery;
			$valuesGallery['userId'] = $uID;

			$idGallery = $presenter->context->createUsersGalleries()
				->insert($valuesGallery);

			$this->addImages($arr, $values, $uID, $idGallery);
			unset($values->agreement);

			//Vložení dat do tabulky activity_stream
			$presenter->context->createStream()->addNewGallery($idGallery, $uID);

			$presenter->flashMessage('Galerie byla vytvořena. Počkejte prosím na schválení adminem.');
			$presenter->redirect('Galleries:');
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}
        
}