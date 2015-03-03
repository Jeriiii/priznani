<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POS\Model\CommentImagesDao;
use POS\Model\LikeImageCommentDao;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro vykreslení komentářů k obrázku.
 *
 * @author Daniel Holubář
 */
class ImageComments extends BaseComments {

	public function __construct(LikeImageCommentDao $likeImageCommentDao, CommentImagesDao $commentImagesDao, $image, $userData, $ownerID, $cachedStreamPreferences = NULL) {
		if ($cachedStreamPreferences instanceof StreamUserPreferences) {
			parent::__construct($likeImageCommentDao, $commentImagesDao, $image, $userData, $ownerID, $cachedStreamPreferences);
		} else {
			parent::__construct($likeImageCommentDao, $commentImagesDao, $image, $userData, $ownerID, NULL);
		}
	}

}
