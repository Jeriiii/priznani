<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\UserImageDao;
use POS\Model\ImageLikesDao;
use POS\Model\LikeCommentDao;
use POS\Model\CommentImagesDao;

class UsersGallery extends BaseGallery {

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * @var \POS\Model\LikeCommentDao
	 */
	public $likeCommentDao;

	/**
	 *
	 * @var \POS\Model\CommentImagesDao
	 */
	public $commentImagesDao;

	/**
	 * @var ActiveRow|ArrayHash $loggedUser
	 */
	public $loggedUser;

	public function __construct($images, $image, $gallery, $domain, $partymode, UserImageDao $userImageDao, ImageLikesDao $imageLikesDao, LikeCommentDao $likeCommentDao, CommentImagesDao $commentImagesDao, $loggedUser) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);
		parent::setUserImageDao($userImageDao);
		$this->imageLikesDao = $imageLikesDao;
		$this->likeCommentDao = $likeCommentDao;
		$this->commentImagesDao = $commentImagesDao;
		$this->loggedUser = $loggedUser;
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
	 * Komponenta pro komentování obrázků
	 * @return \POSComponent\Comments\ImageComments
	 */
	public function createComponentComments() {
		return new \POSComponent\Comments\ImageComments($this->likeCommentDao, $this->commentImagesDao, $this->image, $this->loggedUser);
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
