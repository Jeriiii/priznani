<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Helper;

use Nette\Database\Table\ActiveRow;

/**
 * Description of Image
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Image {

	public $id;
	public $suffix;
	public $galleryID;
	public $userID = NULL;

	public function __construct(ActiveRow $image, $typeGall, $typeImg) {
		if ($typeImg == GetImgPathHelper::TYPE_STREAM) {
			if ($typeGall == GetImgPathHelper::TYPE_USER_GALLERY) {
				$this->id = $image->userGallery->lastImageID;
				$this->userID = $image->userID;
				$this->suffix = $image->userGallery->lastImage->suffix;
				$this->galleryID = $image->userGalleryID;
			} else {
				$this->id = $image->gallery->lastImageID;
				$this->suffix = $image->gallery->lastImage->suffix;
				$this->galleryID = $image->galleryID;
			}
		} else {
			$this->id = $image->id;
			$this->suffix = $image->suffix;
			$this->galleryID = $image->galleryID;
			if ($typeGall == GetImgPathHelper::TYPE_USER_GALLERY) {
				$this->userID = $image->gallery->userID;
			}
		}
	}

}
