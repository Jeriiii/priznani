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
use POS\Model\LikeCommentDao;

class UsersCompetitionsGallery extends BaseGallery {

	/**
	 * @var \POS\Model\UsersCompetitionsDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;

	/**
	 * @var \POS\Model\CommentImagesDao
	 */
	public $commentImagesDao;

	/**
	 * @var \POS\Model\LikeCommentDao
	 */
	public $likeCommentDao;

	public function __construct($images, $image, $gallery, $domain, $partymode, LikeCommentDao $likeCommentDao, UserImageDao $userImageDao, CommentImagesDao $commentImagesDao, ImageLikesDao $imageLikesDao) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);
		parent::setUserImageDao($userImageDao);
		$this->image = $image;
		$this->userImageDao = $userImageDao;
		$this->imageLikesDao = $imageLikesDao;
		$this->commentImagesDao = $commentImagesDao;
		$this->likeCommentDao = $likeCommentDao;
	}

	public function render() {
		parent::renderBaseGallery("../UsersCompetitionsGallery/usersCompetitionsGallery.latte");
	}

	public function createComponentLikes() {
		return new \POSComponent\BaseLikes\ImageLikes($this->imageLikesDao, $this->image, $this->presenter->user->id);
	}

	/**
	 * Komponenta pro komentování obrázků
	 * @return \POSComponent\Comments\ImageComments
	 */
	public function createComponentComments() {
		return new \POSComponent\Comments\ImageComments($this->likeCommentDao, $this->commentImagesDao, $this->image);
	}

}
