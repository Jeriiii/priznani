<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Comments;

use POS\Model\LikeStatusCommentDao;
use POS\Model\CommentStatusesDao;
use POS\UserPreferences\StreamUserPreferences;

/**
 * Komponenta pro vykreslení komentářů k statusu.
 *
 * @author Daniel Holubář
 */
class StatusComments extends BaseComments {

	public function __construct(LikeStatusCommentDao $likeStatusCommentDao, CommentStatusesDao $commentStatusesDao, $status, $userData, $ownerID, $cachedStreamPreferences = NULL) {
		if ($cachedStreamPreferences instanceof StreamUserPreferences) {
			parent::__construct($likeStatusCommentDao, $commentStatusesDao, $status, $userData, $ownerID, $cachedStreamPreferences);
		} else {
			parent::__construct($likeStatusCommentDao, $commentStatusesDao, $status, $userData, $ownerID, NULL);
		}
	}

}
