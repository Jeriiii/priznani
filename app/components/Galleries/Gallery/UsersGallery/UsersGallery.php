<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\UserImageDao;
use POS\Model\ImageLikesDao;

class UsersGallery extends BaseGallery {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	public function __construct($images, $image, $gallery, $domain, $partymode, UserImageDao $userImageDao, ImageLikesDao $imageLikesDao) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);
		parent::setUserImageDao($userImageDao);
		$this->imageLikesDao = $imageLikesDao;
	}

	public function render() {
		parent::renderBaseGallery("../UsersGallery/usersGallery.latte");
	}

	/**
	 * schválí obrázek
	 * @param type $imageID ID obrázku, který se má schválit
	 */
	public function handleApproveImage($imageID) {
		$this->getImages()->approve($imageID);
		$this->setImage($imageID);
	}

	/**
	 * ostranění obrázku
	 * @param type $imageID ID obrázku, který se má odstranit
	 */
	public function handleRemoveImage($imageID) {
		$image = $this->getImages()->find($imageID);

		$folderPath = WWW_DIR . "/images/userGalleries/" . $this->getPresenter()->context->getUser()->getId() . "/" . $image->galleryID . "/";
		$imageFileName = $image->id . "." . $image->suffix;

		parent::removeImage($image, $folderPath, $imageFileName);
	}

	public function createComponentLikes() {
		if ($this->presenter->user->isLoggedIn()) {
			$likes = new \POSComponent\BaseLikes\ImageLikes($this->imageLikesDao, $this->image, $this->presenter->user->id);
		} else {
			$likes = new \POSComponent\BaseLikes\ImageLikes();
		}
		return $likes;
	}

	/**
	 * přepne na další obrázek
	 * @param type $imageID ID dalšího obrázku
	 */
	public function handleNext($imageID) {
		parent::setImage($imageID);
	}

	/**
	 * přepne na předchozí obrázek
	 * @param type $imageID ID předchozího obrázku
	 */
	public function handleBack($imageID) {
		parent::setImage($imageID);
	}

}
