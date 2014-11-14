<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use NetteExt\File;
use NetteExt\Path\GalleryPathCreator;

/**
 * Decription
 */
class VerificationImageNewForm extends UserGalleryImagesBaseForm {

	/**
	 * @var \POS\Model\UserGalleryDao
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\ImageGalleryDao
	 */
	public $userImageDao;
	private $gallery;
	private $images;
	private $userID;

	/**
	 * počet možných polí pro obrázky při vytvoření galerie
	 */
	const NUMBER_OF_IMAGE = 1;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($userGalleryDao, $userImageDao, $streamDao, $parent, $name);

		$this->userID = $this->getPresenter()->getUser()->getId();
		$gallery = $this->userGalleryDao->findVerificationGalleryByUser($this->userID);
		if ($gallery) {
			$this->gallery = $gallery->id;
			$this->images = $this->userImageDao->getUnapprovedImagesInGallery($gallery->id);
		}

		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;

		$this->addGroup("Obrázek");

		$this->addImageFields(self::NUMBER_OF_IMAGE, FALSE, FALSE);

		if (count($this->images) != 0) {
			$this->addCheckbox('erasePrevious', "Při nahrání ověřovacího obrázku budou předchozí neschválené ověřovací obrázky smazány.")
				->addRule(Form::EQUAL, 'Musíte souhlasit se smazáním předchozích neschválených fotek.', TRUE);
		}

		$this->addSubmit("submit", "Přidat ověřovací fotku")->setAttribute('class', 'btn-main medium');

		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(VerificationImageNewForm $form) {
		$values = $form->values;

		$images = $this->getArrayWithImages($values, 1);
		$isFill = $this->isFillImage($images);

		if ($isFill == FALSE) {
			$this->addError("Musíte vybrat soubor");
		} else {
			$presenter = $this->getPresenter();

			if (empty($this->gallery)) {
				$gallery = $this->userGalleryDao->createVerificationGallery($this->userID);
				$this->saveVerificationImage($values, $this->userID, $gallery->id);
			} else {

				$this->deleteImages();
				$this->saveVerificationImage($values, $this->userID, $this->gallery);
			}
			$presenter->flashMessage('Fotka byla přidaná. Nyní je ve frontě na schválení.');
			$presenter->redirect('Show:');
		}
	}

	private function deleteImages() {
		foreach ($this->images as $item) {
			$this->userImageDao->delete($item->id);
			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($item->galleryID, $this->userID);
			File::removeImage($item->id, $item->suffix, $galleryFolder);
		}
	}

}
