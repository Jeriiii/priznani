<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 13.6.2015
 */

namespace NetteExt\Uploader;

use NetteExt\Image;
use Nette\Http\FileUpload;
use Exception;

/**
 * Obrázek, který se má nahrát
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class ImageToUpload {

	/** @var string Uživatelský název obrázku */
	public $name;

	/** @var string Uživatelský popisek obrázku */
	public $description;

	/** @var FileUpload|Image Obrázek */
	public $file;

	/** @var string Suffix */
	public $suffix = null;

	/** @var boolean Jde o profilovou fotku? */
	public $isProfile = false;

	/** @var boolean Má mít vodoznak? */
	public $hasWatermark = true;

	public function __construct($file, $name = '', $description = '') {
		$this->name = $name;
		$this->description = $description;
		$this->checkFileType($file);
		$this->file = $file;
		$this->suffix = $this->getSuffix(); //musí se počítat až po nastavení $name
	}

	/**
	 * Nastaví obrázek, aby se ukládal jako profilová fotka.
	 */
	public function setProfileType() {
		$this->isProfile = true;
		$this->hasWatermark = false;
	}

	/**
	 * Vypočítá suffix obrázku.
	 */
	public function getSuffix() {
		if (empty($this->suffix)) {

			if ($this->file instanceof Image) {
				$suffix = self::suffix($this->name);
				$this->name = '';
			} else {
				$suffix = self::suffix($this->file->getName());
			}
		}

		return $this->suffix;
	}

	/**
	 * Vytvoří suffix z názvu souboru.
	 * @param string $filename
	 * @return string Suffix
	 */
	public static function suffix($filename) {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	private function checkFileType($file) {
		if ((!$file instanceof FileUpload) && (!$file instanceof Image)) {
			throw new Exception('variable $file must by instance of FileUpload or Image');
		}
	}

}
