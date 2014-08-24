<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

use POS\Model\UserImageDao;
use POS\Model\ImageLikesDao;

class UsersCompetitionsGallery extends BaseGallery {

	/**
	 * @var \POS\Model\UsersCompetitionsDao
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\ImageLikesDao
	 */
	public $imageLikesDao;
	protected $image;

	public function __construct($images, $image, $gallery, $domain, $partymode, UserImageDao $userImageDao, ImageLikesDao $imageLikesDao) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);
		parent::setUserImageDao($userImageDao);
		$this->image = $image;
		$this->userImageDao = $userImageDao;
		$this->imageLikesDao = $imageLikesDao;
	}

	public function render() {
		parent::renderBaseGallery("../UsersCompetitionsGallery/usersCompetitionsGallery.latte");
	}

	public function createComponentLikes() {
		if ($this->presenter->user->isLoggedIn()) {
			$likes = new \POSComponent\BaseLikes\ImageLikes($this->imageLikesDao, $this->userImageDao, $this->image, $this->presenter->user->id);
		} else {
			$likes = new \POSComponent\BaseLikes\ImageLikes();
		}
		return $likes;
	}

}
