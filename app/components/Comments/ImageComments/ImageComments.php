<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POS\Model\CommentImagesDao;
use POS\Model\LikeImageCommentDao;

/**
 * Komponenta pro vykreslení komentářů k obrázku.
 *
 * @author Daniel Holubář
 */
class ImageComments extends BaseComments {

	public function __construct(LikeImageCommentDao $likeImageCommentDao, CommentImagesDao $commentImagesDao, $image, $userData) {
		parent::__construct($likeImageCommentDao, $commentImagesDao, $image, $userData);
	}

}
