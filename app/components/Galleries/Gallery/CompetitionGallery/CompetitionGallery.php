<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\ImageDao;
use POS\Model\GalleryDao;
use POS\Model\StreamDao;

class CompetitionGallery extends BaseGallery {

	/**
	 * @var \POS\Model\GalleryDao
	 */
	public $galleryDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\ImageDao
	 * @inject
	 */
	public $imageDao;

	public function __construct($images, $image, $gallery, $domain, $partymode, ImageDao $imageDao, GalleryDao $galleryDao, StreamDao $streamDao) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);

		$this->streamDao = $streamDao;
		$this->galleryDao = $galleryDao;
		$this->imageDao = $imageDao;
		parent::setImageDao($imageDao);
	}

	public function render() {
		parent::renderBaseGallery("../CompetitionGallery/competitionGallery.latte");
	}

	/**
	 * schválí obrázek
	 * @param type $imageID ID obrázku, který se má schválit
	 */
	public function handleApproveImage($imageID) {
		$this->imageDao->approve($imageID);

		$image = $this->imageDao->find($imageID);
		$this->galleryDao->updateLastImage($image->galleryID, $image->id);

		$this->streamDao->aliveCompGallery($image->galleryID);

		$this->setImage($imageID, $this->imageDao);
	}

	/**
	 * ostranění obrázku
	 * @param type $imageID ID obrázku, který se má odstranit
	 */
	public function handleRemoveImage($imageID) {
		$image = $this->imageDao->find($imageID);

		$folderPath = WWW_DIR . "/images/galleries/" . $image->galleryID . "/";
		$imageFileName = $image->id . "." . $image->suffix;

		parent::removeImage($image, $folderPath, $imageFileName);
		$image->delete();
	}

}
