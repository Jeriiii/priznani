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
	 * @var String
	 */
	public $parentName;

	public function __construct($tempPath, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->tempPath = $tempPath;
		$this->parentName = $parent->name;

		$this->addGroup('Nahrát profilové foto');
		$this->addUpload('imageToUpload', 'Přidat fotku:')
			->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 * 1024)
			->addCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
		$this->addSubmit('uploadImageToCrop', 'Nahrát');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$image = $values->imageToUpload;
		if ($image->isOK() & $image->isImage()) {
			$imagePath = UploadImage::uploadToTemp($image, 1000, 1000);

			$this->getPresenter()->redirect('this', array(
				$this->parentName . '-crop-image-path' => $imagePath
			));
		} else {
			$this->addError("Vyberte platný soubor");
		}
	}

}
