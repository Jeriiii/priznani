<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POSComponent\BaseLikes;

use POS\Model\ImageLikesDao;
use POS\Model\UserImageDao;

/**
 * Description of ImageLikes
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImageLikes extends BaseLikes implements IBaseLikes {

	public $imageLikesDao;
	public $userImageDao;
	public $image;

	public function __construct(ImageLikesDao $imageLikesDao = NULL, UserImageDao $userImageDao = NULL, $image = NULL, $userID = NULL) {
		$this->image = $image;
		parent::__construct($imageLikesDao, $this->image, $userID);
		$this->imageLikesDao = $imageLikesDao;
		$this->userImageDao = $userImageDao;
	}

	public function render() {
		parent::render();
	}

	public function handleSexy($userID, $imageID) {
		$this->imageLikesDao->addLiked($imageID, $userID);
		$this->image = $this->userImageDao->addLike($imageID);

		$this->redrawControl();
	}

}
