<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Vyčistí nacachovaná data
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Install;

use NetteExt\File;

class ClearCasch {

	private $cacheDirs = array();

	/** @var Messages */
	private $messages;

	public function __construct($messages = NULL) {
		if (isset($messages)) {
			$this->messages = $messages;
		} else {
			$this->messages = new Messages;
		}

		$basePath = WWW_DIR;
		$this->cacheDirs[] = $basePath . "/cache/css";
		$this->cacheDirs[] = $basePath . "/cache/js";
		$this->cacheDirs[] = $basePath . "/../temp/cache";
	}

	/**
	 * Vyčistí cache - smaže vše v cache složkách
	 */
	public function clearCache() {
		foreach ($this->cacheDirs as $dir) {
			File::clearDir($dir);
			$this->messages->addMessage("Cache $dir byla vyčištěna");
		}
	}

}
