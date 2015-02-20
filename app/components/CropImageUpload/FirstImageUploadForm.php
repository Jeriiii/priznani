<?php

namespace Nette\Application\UI\Form;

use NetteExt\Form\Upload\UploadImage;
use Nette\Forms\Form;

/**
 * Formulář pro fotek, co se budou ořezávat.
 */
class FirstImageUploadForm extends BaseForm {

	/**
	 * Cesta do adresáře, kam se budou ukládat nahrané fotografie
	 * Musí končit lomítkem
	 * @var String
	 */
	public $tempPath;

	/**
	 * @var \Nette\ComponentModel\IComponent
	 */
	public $parent;

	public function __construct($tempPath, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);


		$this->tempPath = $tempPath;
		$this->parent = $parent;

		$this->addUpload('imageToUpload', 'Přidat fotku:')
			->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
			->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
			->addCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
		$this->addSubmit('uploadImageToCrop', 'Nahrát');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$image = $values->imageToUpload;
		if ($image->isOK()) {
			$imagePath = UploadImage::uploadToTemp($image, 50, 50, 1000, 1000);
		} else {
			$this->addError("Vyberte platný soubor");
			$this->presenter->redirect('this');
		}

		$link = $this->parent->link('crop!', array(
			'path' => $imagePath, //cesta
		));

		$this->presenter->redirectUrl($link);
	}

}
