<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POS\Model\LikeConfessionCommentDao;
use POS\Model\CommentConfessionsDao;

/**
 * Komponenta pro vykreslení komentářů k statusu.
 *
 * @author Daniel Holubář
 */
class ConfessionComments extends BaseComments {

	public function __construct(LikeConfessionCommentDao $likeConfessionCommentDao, CommentConfessionsDao $commentConfessionsDao, $confession, $userData) {
		parent::__construct($likeConfessionCommentDao, $commentConfessionsDao, $confession, $userData, 0);
	}

}
