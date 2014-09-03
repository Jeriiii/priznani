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
		parent::__construct($imageLikesDao, $image, $userID);
		$this->image = $image;
		$this->imageLikesDao = $imageLikesDao;
		$this->userImageDao = $userImageDao;
	}

	public function handleSexy($userID, $imageID) {
		if ($this->liked == FALSE) {
			$this->imageLikesDao->addLiked($imageID, $userID);
		}
		$this->liked = $this->getLikedByUser($this->userID, $this->image->id);

		$this->redrawControl();
	}

	/**
	 *
	 * @param int $userID ID užovatele, kterého hledáme
	 * @param int $imageID ID obrázku, který hledáme
	 * @return bool
	 */
	public function getLikedByUser($userID, $imageID) {
		$liked = $this->imageLikesDao->likedByUser($userID, $imageID);
		return $liked;
	}

}
