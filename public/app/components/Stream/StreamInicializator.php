<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 28.4.2015
 */

namespace POSComponent\Stream;

use NetteExt\DaoBox;
use POSComponent\Stream\UserStream\UserStream;
use POS\UserPreferences\StreamUserPreferences;
use Nette\Security\User;

/**
 * Správně nastaví komponentu streamu.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class StreamInicializator {

	/**
	 * Vytvoří a vrátí user stream.
	 * @param \Nette\Application\UI\Presenter $presenter Presenter, ze kterého se čtou dao.
	 * @param \Nette\Http\Session $session
	 * @param \Nette\Security\User $user Přihlášený uživatel
	 * @param bool $isLoggedIn Je uživatel přihlášen?
	 * @param \Nette\Database\Table\ActiveRow|\Nette\ArrayHash $loggedUser Přihlášený uživatel.
	 * @return UserStream
	 */
	public function createUserStream($presenter, $session, User $user, $loggedUser = null) {
		$dataForStream = $this->getCorrectDataForStream($presenter, $session, $user, $loggedUser);
		$daoBox = $this->getDaoBoxUserStream($presenter);

		return new UserStream($dataForStream, $daoBox, $session, $loggedUser, $user);
	}

	/**
	 * Nastavý preferované příspěvky uživatele.
	 * @return StreamUserPreferences
	 */
	private function createStreamUserPreferences($presenter, $session, $loggedUser) {
		return new StreamUserPreferences($loggedUser, $presenter->userDao, $presenter->streamDao, $presenter->userCategoryDao, $session);
	}

	/**
	 * Vrátí preferované příspěvky uživatele do streamu.
	 * @return StreamUserPreferences
	 */
	private function getCorrectDataForStream($presenter, $session, User $user, $loggedUser) {
		if ($user->isLoggedIn() && isset($loggedUser->property)) {
			$streamPreferences = $this->createStreamUserPreferences($presenter, $session, $loggedUser);
			return $streamPreferences->getBestStreamItems();
		} else {
			return $presenter->streamDao->getForUnloggedUser("DESC");
		}
	}

	/**
	 * Vrátí DaoBox naplněný pro user stream.
	 */
	private function getDaoBoxUserStream($presenter) {
		$daoBox = new DaoBox;

		$daoBox->likeStatusDao = $presenter->likeStatusDao;
		$daoBox->imageLikesDao = $presenter->imageLikesDao;
		$daoBox->userDao = $presenter->userDao;
		$daoBox->statusDao = $presenter->statusDao;

		$daoBox->streamDao = $presenter->streamDao;
		$daoBox->userGalleryDao = $presenter->userGalleryDao;
		$daoBox->userImageDao = $presenter->userImageDao;
		$daoBox->confessionDao = $presenter->confessionDao;

		$daoBox->userPositionDao = $presenter->userPositionDao;
		$daoBox->enumPositionDao = $presenter->enumPositionDao;
		$daoBox->userPlaceDao = $presenter->userPlaceDao;
		$daoBox->enumPlaceDao = $presenter->enumPlaceDao;

		$daoBox->likeImageCommentDao = $presenter->likeImageCommentDao;
		$daoBox->commentImagesDao = $presenter->commentImagesDao;
		$daoBox->likeStatusCommentDao = $presenter->likeStatusCommentDao;
		$daoBox->commentStatusesDao = $presenter->commentStatusesDao;

		$daoBox->likeConfessionCommentDao = $presenter->likeConfessionCommentDao;
		$daoBox->commentConfessionsDao = $presenter->commentConfessionsDao;
		$daoBox->likeConfessionDao = $presenter->likeConfessionDao;
		$daoBox->usersNewsDao = $presenter->usersNewsDao;

		$daoBox->rateImageDao = $presenter->rateImageDao;
		$daoBox->imageLikesDao = $presenter->imageLikesDao;
		$daoBox->userCategoryDao = $presenter->userCategoryDao;

		return $daoBox;
	}

}
