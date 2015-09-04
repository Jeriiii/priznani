<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\CropImageUpload;

use Nette\Application\UI\Form\BaseForm;
use POSComponent\BaseProjectControl;
use Nette\Application\UI\Form as Frm;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use POS\Ext\ImageRules;
use NetteExt\Uploader\ImageUploader;

/**
 * Zprostředkovává upload obrázku, který posléze umožňuje oříznout
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class CropImageUpload extends BaseProjectControl {

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/** @var ImageUploader	 */
	public $imageUploader;

	/**
	 * Cesta k předtím nahranému obrázku
	 * @var string
	 */
	public $cropImageName = NULL;

	/**
	 * Standardní konstruktor
	 */
	function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, ImageUploader $imageUploader, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
		$this->userGalleryDao = $userGalleryDao;
		$this->imageUploader = $imageUploader;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/cropImageUpload.latte');
		$template->onMobile = $this->getDeviceDetector()->isMobile();
		if (!empty($this->cropImageName)) {
			$template->uploadedImageName = $this->cropImageName;
		}

		$template->rules = ImageRules::$rules;
		$template->render();
	}

	/**
	 * Zpracování signálu, že chceme editovat obrázek, který jsme předtím nahráli
	 * @param type $path cesta k obrázku
	 */
	public function handleCrop($path) {
		$this->cropImageName = $path;
	}

	/**
	 * * Komponenta formuláře pro prvotní upload
	 * @param type $name
	 * @return \Nette\Application\UI\Form\BaseForm
	 */
	protected function createComponentAfterUploadForm($name) {
		return new Frm\ProfilePhotoUploadForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this->imageUploader, $this, $name);
	}

	/**
	 * komponenta formuláře předaného v konstruktoru
	 * @param type $name
	 * @return \Nette\Application\UI\Form\BaseForm
	 */
	protected function createComponentFirstImageUploadForm($name) {
		if ($this->getDeviceDetector()->isMobile()) {
			return new Frm\SimpleProfilePhotoUploadForm($this->userGalleryDao, $this->userImageDao, $this->streamDao, $this->imageUploader, $this, $name);
		} else {
			return new \Nette\Application\UI\Form\FirstImageUploadForm(WWW_DIR . '/image-temp/', $this, $name);
		}
	}

}
