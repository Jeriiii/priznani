<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image,
        Nette\Utils\Strings;

class UserGalleryNewForm extends UserGalleryImagesBaseForm {

	public $id_gallery;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		//graphics
//		$renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = NULL;
		//form

		$this->addGroup('Infromace o galerii');
		$this->addText("name", "Jméno galerie:", 30, 35)
			->addRule(Form::FILLED, "Musíte zadat jméno galerii.")
			->addRule(Form::MAX_LENGTH, "Maximální délka jména galerie je %d znaků", 150);
		$this->addTextArea("description_gallery", "Popis galerie", 30, 6)
			->addRule(Form::MAX_LENGTH, "Maximální délka komentáře je %d znaků", 500);


		$this->addGroup('Fotografie (4 x 4MB)');

		$this->addImagesFile(4);


		$this->addCheckbox("agreement", Html::el('a')
				->href("http://priznanizparby.cz/soutez/fotografie.pdf")
				->setHtml('Souhlasím s podmínkami'))
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");


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
			$valuesGallery['description'] = $values->description_gallery;
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
