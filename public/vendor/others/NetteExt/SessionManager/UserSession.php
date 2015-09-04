<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 1.6.2015
 */

namespace NetteExt\Session;

use Nette\Database\Table\ActiveRow;
use Nette\Http\Session;
use NetteExt\Serialize\Relation;
use NetteExt\Serialize\Serializer;
use POS\Model\UserDao;
use POS\Model\PaymentDao;
use POS\Model\UserImageDao;
use POS\Model\UserGalleryDao;

/**
 * Zajišťuje operace nad data v sečně přímo spojovaná s uživatelem (jméno, profilová fotka ...).
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserSession {
	/* datum určující pomezí - pokud se uživatel registroval před tímto datem, nebudou mu rozmazávány fotky */

	const DATE_LINE_FOR_BLURRY_IMAGES = '2015-08-21 00:00:00';

	/**
	 * Přepočítá data o přihlášeném uživateli.
	 * @param ActiveRow $loggedUser
	 * @param Session $session
	 */
	public static function calculateLoggedUser(UserDao $userDao, $loggedUser, Session $session, PaymentDao $paymentDao, UserImageDao $userImageDao, UserGalleryDao $userGalleryDao) {
		$user = $userDao->getUser($loggedUser->id);

		$section = self::getSectionLoggedUser($session);
		$section->setExpiration('20 minutes');

		$relProfilPhoto = new Relation("profilFoto");
		$relGallery = new Relation("gallery");
		$relProperty = new Relation("property");
		$relCouple = new Relation("couple");
		$relProfilPhoto->addRel($relGallery);

		$ser = new Serializer($user);
		$ser->addRel($relProfilPhoto);
		$ser->addRel($relProperty);
		$ser->addRel($relCouple);

		$sel = (array) $ser->toArrayHash();
		/* vytazeni jen jednoho radku */
		$userRow = array_shift($sel);
		$userRow->isPaying = $paymentDao->isUserPaying($loggedUser->id);
		$userRow->blurryImages = self::shouldImagesBeBlurry($userRow, $userImageDao, $userGalleryDao);
		$section->loggedUser = $userRow;
	}

	/**
	 * Vrátí sekci přihlášeného uživatele.
	 * @return Nette\Http\Session|Nette\Http\SessionSection
	 */
	public static function getSectionLoggedUser(Session $session) {
		$sectionLoggedUser = $session->getSection('loggedUser');
		return $sectionLoggedUser;
	}

	/**
	 * Vrátí rozhodnutí, zda se mají rozmazávat fotky ostatních
	 * @param  ArrayHash $userRow uživatel, kterého se to týká
	 * @return bool rozmazávat?
	 */
	public static function shouldImagesBeBlurry($userRow, UserImageDao $userImageDao, UserGalleryDao $userGalleryDao) {
		$photos = $userImageDao->getAllFromUser($userRow->id, $userRow->id, $userGalleryDao);
		$hasApprovedPhoto = FALSE; /* když je TRUE, fotky se nebudou rozmazávat */
		foreach ($photos as $photo) {
			if (TRUE) {/* TRUE, protože stačí mít jednu nahranou. Tady je možné změnit podmínku, jaké mají být uživatelovy fotky - např. na $photo->approved == 1 && $photo->checkApproved == 1 */
				$hasApprovedPhoto = TRUE;
				break;
			}
		}
		return !($userRow->created < self::DATE_LINE_FOR_BLURRY_IMAGES || $userRow->isPaying || $hasApprovedPhoto);
	}

}
