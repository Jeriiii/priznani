<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 13.6.2015
 */

namespace NetteExt\Uploader;

use NetteExt\Image;
use NetteExt\Uploader\ImageToUpload;
use NetteExt\Uploader\ImagesToUpload;
use POS\Model\UserGalleryDao;
use POS\Model\UserImageDao;
use POS\Model\StreamDao;
use NetteExt\Form\Upload\UploadImage;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\Watermarks;
use NetteExt\File;
use Nette\Http\FileUpload;
use NetteExt\Path\ImagePathCreator;
use POS\Model\UserDao;
use POS\Model\ActivitiesDao;
use Nette\Object;

/**
 * Třída sloužící pro nahrávání obrázků.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImageUploader extends Object {

	/** Událost nastávající po uploadu fotky uživatelem	 */
	public $onImageUpload = array();

	/** @var \POS\Model\UserGalleryDao */
	public $userGalleryDao;

	/** @var \POS\Model\UserImageDao */
	public $userImageDao;

	/** @var \POS\Model\StreamDao */
	public $streamDao;

	/** @var \POS\Model\ActivitiesDao */
	public $activitiesDao;

	/** @var int Pokud má uživatel alespoň 1 schválené fotky, schvaluj další automaticky */
	const AllowLimitForImages = 1;

	public function __construct(UserGalleryDao $userGalleryDao, UserImageDao $userImageDao, StreamDao $streamDao, ActivitiesDao $activitiesDao) {
		$this->userGalleryDao = $userGalleryDao;
		$this->userImageDao = $userImageDao;
		$this->streamDao = $streamDao;
		$this->activitiesDao = $activitiesDao;
	}

	/**
	 * Uloží obrázky do databáze a na disk.
	 * @param ImagesToUpload $imagesToUpload Obrázky k uložení.
	 * @param \Nette\ArrayHash $values Všechny hodnoty z formuláře.
	 * @return boolean TRUE pokud byly fotky automaticky schválené, jinak FALSE
	 */
	public function saveImages(ImagesToUpload $imagesToUpload) {
		//získání počtu user obrázků, které mají allow 1
		$allowedImagesCount = $this->userImageDao->countAllowedImages($imagesToUpload->getUserID());

		//pokud je 1 a více schválených, schválí i nově přidávanou
		$allow = $allowedImagesCount >= self::AllowLimitForImages ? TRUE : FALSE;

		$images = $imagesToUpload->getImages();
		$lastImage = NULL;

		foreach ($images as $image) {
			$imageRow = $this->saveImage($image, $allow, $imagesToUpload->getUserID(), $imagesToUpload->getGalleryID());
			$lastImage = $imageRow !== NULL ? $imageRow : $lastImage;
		}

		/* vytvoří aktivitu že uživatel nahrál obrázek */
		if (!empty($lastImage)) {
			$this->activitiesDao->createImageActivity($imagesToUpload->getUserID(), NULL, $lastImage->id, ActivitiesDao::TYPE_ADD_NEW);
		}

		$this->onImageUpload($lastImage->approved);
		return $lastImage->approved;
	}

	/**
	 * Uloží obrázek do databáze a na disk.
	 * @param ImageToUpload $image Obrázek, co se má uložit.
	 * @param boolean $allow Může se zveřejnit? Ještě se ovlivňuje tím, zda nejde o profilovou fotku.
	 * @param int $userID ID uživatele.
	 * @param int $galleryID ID galerie.
	 * @return boolean Smí se zveřejnit? Ovlivní se pokud jde o profilovou fotku.
	 */
	private function saveImage(ImageToUpload $image, $allow, $userID, $galleryID) {
		if ($image->file instanceof Image || $image->file->isOK()) {
			if ($image->isProfile == TRUE) {
				$allow = TRUE;
			}

			//Uloží obrázek do databáze
			$imageRow = $this->saveImageToDB($image, $galleryID, $allow);

			$this->upload($image, $imageRow->id, $galleryID, $userID, 525, 700, 100, 130);

			//zaznamenání velikosti screnu do proměných width/heightGalScrn
			$this->changeSizeGalScrnDB($galleryID, $userID, $imageRow->id, $image->suffix);

			return $imageRow;
		}

		return NULL;
	}

	/**
	 * Uloží obrázek do databáze.
	 * @param int $galleryID ID galerie.
	 * @param string $name Název obrázku zadaný uživatelem.
	 * @param string $description Popis obrázku zadaný uživatelem.
	 * @param string $suffix Koncovka obrázku.
	 * @param boolean $allow Automatické schvalování obrázků.
	 * @param boolean $profilePhoto TRUE = jde o profilovou fotku jinak FALSE
	 * @return Database\Table\IRow
	 */
	private function saveImageToDB(ImageToUpload $image, $galleryID, $allow) {
		$approved = $allow == TRUE ? 1 : 0;
		$checkApproved = $approved;
		$imageRow = $this->userImageDao->insertImage($image->name, $image->suffix, $image->description, $galleryID, $approved, $checkApproved);
		$this->userGalleryDao->updateBestAndLastImage($galleryID, $imageRow->id, $imageRow->id);

		//aktualizace streamu - vyhodí galerii ve streamu nahoru
		if ($allow) {
			$user = $imageRow->gallery->user;
			$this->streamDao->aliveGallery($imageRow->galleryID, $user->id, $user->property->preferencesID);
		}
		/* nastavení fotky jako profilové */
		if ($image->isProfile) {
			$imageRow->gallery->user->update(array(
				UserDao::COLUMN_PROFIL_PHOTO_ID => $imageRow->id
			));
		}
		return $imageRow;
	}

	/**
	 * Uloží obrázek do souboru, pokud je v pořádku.
	 * @param ImageToUpload $image Obrázek, co se má uložit na disk
	 * @param int $id ID obrázku v databázi.
	 * @param string $suffix Koncovka obrázku v databázi.
	 * @param string $folder Složka galerie.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param int $max_height Maximální výška screenu.
	 * @param int $max_width Maximální šířka screenu.
	 * @param int $max_minheight Maximální výška miniatury.
	 * @param int $max_minwidth Maximální šířka miniatury.
	 * @param bool $addWatermark přidání/nepřidání watermarku
	 */
	private function upload(ImageToUpload $image, $id, $galleryID, $userID, $max_height, $max_width, $max_minheight, $max_minwidth) {
		if ($image->file instanceof Image || ($image->file->isOK() && $image->file->isImage())) {
			$this->checkGallDirs($userID, $galleryID);

			/* uložení souboru a renačtení */
			$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryID, $userID);

			$paths;
			if ($image->file instanceof FileUpload) {
				$paths = UploadImage::upload(
						$image->file, $id, $image->suffix, $galleryFolder, $max_height, $max_width, $max_minheight, $max_minwidth);
			} else if ($image->file instanceof Image) {
				$paths = UploadImage::moveImage(
						$image->file, $id, $image->suffix, $galleryFolder, $max_height, $max_width, $max_minheight, $max_minwidth);
			}

			$this->addWatermark($image, $paths);
		}
	}

	/**
	 * Zaznamenání velikosti gal. screenu do DB po resizu obrázku.
	 * @param int $galleryID ID galerie.
	 * @param int $userID ID uživatele.
	 * @param int $imageID ID obrázku.
	 * @param string $suffix Přípona obrázku.
	 */
	private function changeSizeGalScrnDB($galleryID, $userID, $imageID, $suffix) {
		$galleryFolder = GalleryPathCreator::getUserGalleryFolder($galleryID, $userID);
		$imagePath = ImagePathCreator::getImgScrnPath($imageID, $suffix, $galleryFolder);
		$imageFile = Image::fromFile($imagePath);
		$this->userImageDao->update($imageID, array(
			UserImageDao::COLUMN_GAL_SCRN_HEIGHT => $imageFile->height,
			UserImageDao::COLUMN_GAL_SCRN_WIDTH => $imageFile->width,
		));
	}

	/**
	 * Vytvoří složku pro uživatele a galerii, pokud neexistuje
	 * @param int $userID Id uživatele.
	 * @param int $galleryID Id galerie.
	 */
	private function checkGallDirs($userID, $galleryID) {
		$galleriesPath = GalleryPathCreator::getBaseUserGalleryPath($userID);
		$galleryPath = GalleryPathCreator::getUserGalleryPath($galleryID, $userID);
		File::createDir($galleriesPath); // složka uživatele
		File::createDir($galleryPath); // složka galerie
	}

	/**
	 * Přidá watermark k obrázku.
	 * @param ImageToUpload $image Obrázek.
	 * @param array $paths Všechny verze nahraného obrázku.
	 */
	private function addWatermark(ImageToUpload $image, array $paths) {
		if ($image->hasWatermark) {
			foreach ($paths as $path) {
				Watermarks::addFullWatermark($path, WWW_DIR . '/images/watermarks/mark_pos.png');
				Watermarks::addBottomRightWatermark($path, WWW_DIR . '/images/watermarks/domain_pos.png', 10, 10, 100, 3);
			}
		}
	}

}
