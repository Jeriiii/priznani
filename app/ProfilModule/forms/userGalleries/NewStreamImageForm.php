<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;

class NewStreamImageForm extends NewImageForm {

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		//form
		$this->addImagesFile(3);
		
		$this->onValidate[] = callback($this, 'checkboxValidation');
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
			$defaultGallery = $presenter->context->createUsersGalleries()->where(array("userID" => $uID, "default" => 1))->fetch();
			
			$galleryValues['man'] = $values->man;
			$galleryValues['women'] = $values->women;
			$galleryValues['couple'] = $values->couple;
			$galleryValues['more'] = $values->more;
			
			$gallery = $presenter->context->createUsersGalleries()->where('id', $defaultGallery->id)->fetch();
			$gallery->update($galleryValues);
			

			//$arr = array($image, $image2, $image3, $image4);

			$this->addImages($arr, $values, $uID, $defaultGallery->id);

			//aktualizování dat v tabulce activity_stream
			//$presenter->context->createStream()->aliveGallery($idGallery, $uID);

			$presenter->flashMessage('Fotky byly přidané.');
			$presenter->redirect('Galleries:listUserGalleryImages', array("galleryID" => $defaultGallery->id));
		}
	}

	public function getPres() {
		return $this->getPresenter();
	}

	
	public function checkboxValidation($form) {
		$values = $form->getValues();
		
		if(empty($values['man']) && empty($values['women']) && empty($values['couple']) && empty($values['more'])) {
			$form->addError("Musíte vybrat jednu z kategorií");
		}
	}
}
