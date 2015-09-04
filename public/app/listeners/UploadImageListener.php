<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

/**
 * Slouží k provedení operací, které se mají uskutečnit pokaždé, když uživatel nahraje nějaký obrázek
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class UploadImageListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	public function __construct() {

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

	public function onImageUpload() {

	}

}
