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

		$this->addSubmit("submit", "Přidat fotky")->setAttribute('class','btn-main medium');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(NewImageForm $form) {
		$values = $form->values;
		$image = $values->foto0;
		$image2 = $values->foto1;
		$image3 = $values->foto2;
		$image4 = $values->foto3;

		if ($image->error != 0 && $image2->error != 0 && $image3->error != 0 && $image4->error != 0) {
			$this->addError("Musíte vybrat alespoň 1 soubor");
		} else {

			$presenter = $this->getPres();
			$uID = $presenter->getUser()->getId();
			$idGallery = $values->galleryID;


			$arr = array($image, $image2, $image3, $image4);

			$this->addImages($arr, $values, $uID, $idGallery);

			//aktualizování dat v tabulce activity_stream
			$presenter->context->createStream()->aliveGallery($idGallery, $uID);

			$presenter->flashMessage('Fotky byly přidané.');
			$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $idGallery));
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}

}
