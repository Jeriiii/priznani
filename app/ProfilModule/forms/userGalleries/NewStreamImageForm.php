<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;

class NewStreamImageForm extends UserGalleryImagesBaseForm {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		//form
		$this->addImagesFile(3, TRUE, FALSE);
		
		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class', 'submit-button');
		
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewStreamImageForm $form) {
		$values = $form->values;
		$num = $this->getNumberOfPhotos($values);

        $arr = $this->getArrayWithPhotos($values, $num);

        $isOK = $this->getOkUploadedPhotos($arr);

		if ($isOK == FALSE) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPres();
			$uID = $presenter->getUser()->getId();
			$defaultGallery = $presenter->context->createUsersGalleries()->where(array("userID" => $uID, "default" => 1))->fetch();

			//$arr = array($image, $image2, $image3, $image4);

			$this->addImages($arr, $values, $uID, $defaultGallery->id);

			//aktualizování dat v tabulce activity_stream
			//$presenter->context->createStream()->aliveGallery($idGallery, $uID);

			$presenter->flashMessage('Fotky byly přidané.');
			$presenter->redirect('OnePage:default');
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}
}
