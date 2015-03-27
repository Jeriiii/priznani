<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\UserImageDao;
use POS\Model\ImageLikesDao;
use POS\Model\CommentImagesDao;
use POS\Model\LikeImageCommentDao;
use POSComponent\Comments\ImageComments;
use POSComponent\BaseLikes\ImageLikes;

class UsersCompetitionsGallery extends BaseGallery {

	/** @var \POS\Model\ImageLikesDao */
	public $imageLikesDao;

	/** @var \POS\Model\CommentImagesDao */
	public $commentImagesDao;

	/** @var \POS\Model\LikeImageCommentDao */
	public $likeImageCommentDao;

	/** @var ActiveRow|ArrayHash $loggedUser */
	public $loggedUser;

	/** @var int ID uživatele, kterému patří obrázek. */
	private $ownerID;

	public function __construct($images, $image, $gallery, LikeImageCommentDao $likeImageCommentDao, UserImageDao $userImageDao, CommentImagesDao $commentImagesDao, ImageLikesDao $imageLikesDao, $loggedUser, $parent, $name) {
		parent::__construct($images, $image, $gallery, $parent, $name);
		parent::setUserImageDao($userImageDao);
		$this->image = $image;
		$this->imageLikesDao = $imageLikesDao;
		$this->commentImagesDao = $commentImagesDao;
		$this->likeImageCommentDao = $likeImageCommentDao;
		$this->loggedUser = $loggedUser;
		$this->ownerID = 0; //neexistuje vlastník
	}

	public function render() {
		parent::renderBaseGallery("../UsersCompetitionsGallery/usersCompetitionsGallery.latte");
	}

	public function createComponentLikes() {
		return new ImageLikes($this->imageLikesDao, $this->image, $this->loggedUser->id, $this->ownerID);
	}

	/**
	 * Komponenta pro komentování obrázků
	 * @return \POSComponent\Comments\ImageComments
	 */
	public function createComponentComments() {
		$imageComments = new ImageComments($this->likeImageCommentDao, $this->commentImagesDao, $this->image, $this->loggedUser, $this->ownerID);
		$imageComments->setPresenter($this->getPresenter());
		return $imageComments;
	}

}
