<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;

class NewImageForm extends UserGalleryImagesBaseForm {

	public $galleryID;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		//form
		$presenter = $this->getPresenter();
		$this->galleryID = $presenter->getParam('galleryID');


		$this->addGroup('Fotografie (4 x 4MB)');

		$this->addImagesFile(4);

		$this->addHidden('galleryID', $this->galleryID);
		
		$this->genderCheckboxes();

		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class', 'btn-main medium');
		
		$this->onValidate[] = callback($this, 'genderCheckboxValidation');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewImageForm $form) {
		$values = $form->values;
		$num = $this->getNumberOfPhotos($values);

                $arr = $this->getArrayWithPhotos($values, $num);

                $isOK = $this->getOkUploadedPhotos($arr);

		if ($isOK == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPres();
			$uID = $presenter->getUser()->getId();
			$idGallery = $values->galleryID;
			
			$galleryValues['man'] = $values->man;
			$galleryValues['women'] = $values->women;
			$galleryValues['couple'] = $values->couple;
			$galleryValues['more'] = $values->more;
			
			$gallery = $presenter->context->createUsersGalleries()->where('id', $idGallery)->fetch();
			$gallery->update($galleryValues);
			

			//$arr = array($image, $image2, $image3, $image4);

			$this->addImages($arr, $values, $uID, $idGallery);

			//aktualizování dat v tabulce activity_stream
			//$presenter->context->createStream()->aliveGallery($idGallery, $uID);

			$presenter->flashMessage('Fotky byly přidané.');
			$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $idGallery));
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}
}
