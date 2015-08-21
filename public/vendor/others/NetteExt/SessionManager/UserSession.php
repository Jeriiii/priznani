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

/**
 * Zajišťuje operace nad data v sečně přímo spojovaná s uživatelem (jméno, profilová fotka ...).
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class UserSession {

	/**
	 * Přepočítá data o přihlášeném uživateli.
	 * @param ActiveRow $loggedUser
	 * @param Session $session
	 */
	public static function calculateLoggedUser(UserDao $userDao, $loggedUser, Session $session) {
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
		$userRow->blurryImages = self::shouldImagesBeBlurry($userRow);
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
	public static function shouldImagesBeBlurry($userRow){
		return TRUE;
	}

}
