<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POS\Model\CommentImagesDao;
use POS\Model\LikeCommentDao;

/**
 * Komponenta pro vykreslení komentářů k obrázku.
 *
 * @author Daniel Holubář
 */
class ImageComments extends BaseComments {

	public function __construct(LikeCommentDao $likeCommentDao, CommentImagesDao $commentImagesDao, $image, $userData) {
		parent::__construct($likeCommentDao, $commentImagesDao, $image, $userData);
	}

}
