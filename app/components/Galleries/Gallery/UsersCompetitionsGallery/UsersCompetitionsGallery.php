<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 *
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

class UsersCompetitionsGallery extends BaseGallery {

	/**
	 * @var \POS\Model\UsersCompetitionsDao
	 */
	public $usersCompetitionsDao;

	public function __construct($images, $image, $gallery, $domain, $partymode, \POS\Model\UserImageDao $userImageDao) {
		parent::__construct($images, $image, $gallery, $domain, $partymode);
		parent::setUserImageDao($userImageDao);
	}

	public function render() {
		parent::renderBaseGallery("../UsersCompetitionsGallery/usersCompetitionsGallery.latte");
	}

}
