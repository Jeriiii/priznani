<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

use NetteExt\Session\UserSession;
use Nette\Http\Session;

/**
 * Slouží k provedení operací, které se mají uskutečnit pokaždé, když uživatel nahraje nějaký obrázek
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class UploadImageListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/** @var Nette\Http\Session */
	private $session;

	public function __construct(Session $session) {
		$this->session = $session;
	}

	/**
	 * Implementace interfacu.
	 * @return array Vrací pole objektů a jejich událostí, nad kterými bude naslouchat
	 */
	public function getSubscribedEvents() {

		return array(
			'NetteExt\Uploader\ImageUploader::onImageUpload'
		);
	}

	/**
	 * Nastaví sešně proměnnou loggedUser na NULL a tak donutí aplikaci proměnnou přepočítat
	 */
	public function onImageUpload() {
		$section = UserSession::getSectionLoggedUser($this->session);
		$section->loggedUser = NULL;
	}

}
