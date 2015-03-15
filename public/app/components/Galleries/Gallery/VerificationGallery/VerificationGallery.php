<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\UserImageDao;
use POS\Model\ImageLikesDao;

class VerificationGallery extends BaseGallery {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;
	//platící uživatel
	protected $paying;

	public function __construct($images, $image, $gallery, $domain, $partymode, UserImageDao $userImageDao, ImageLikesDao $imageLikesDao, $paying, $parent, $name) {
		parent::__construct($images, $image, $gallery, $domain, $partymode, $parent, $name);
		parent::setUserImageDao($userImageDao);

		$this->imageLikesDao = $imageLikesDao;
		$this->paying = $paying;
	}

	public function render() {
		$this->template->paying = $this->paying;
		$this->template->userID = $this->presenter->user->id;
		parent::renderBaseGallery("../VerificationGallery/VerificationGallery.latte");
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

		$this->flashMessage("Ověřovací fotka smazána");
		$this->redirect("Show:");
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
