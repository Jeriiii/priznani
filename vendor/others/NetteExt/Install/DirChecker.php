<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace NetteExt\Install;

/**
 * Stará se o kontrolu všech složek v projektu - zda exoistují. Pokud ne
 * vytvoří je.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class DirChecker {
	/* adresáře, které by měli existovat */

	private $dirs = array();

	/**
	 * Zkontroluje zda složky existují. Pokud ne, vytvoří je.
	 */
	public function check() {
		// přidá složky ke skontrolování
		$this->addDirs();

		//zkontroluje složky
		$this->checkDirs();
	}

	/**
	 * Přidá složky ke zkontrolování.
	 */
	private function addDirs() {
		// přidání složek pro galerie
		$this->addToExistDirs(WWW_DIR . "/images/galleries/");
		$this->addToExistDirs(WWW_DIR . "/images/userGalleries/");
		$this->addToExistDirs(WWW_DIR . "/images/users/profils/");

		// přidání složek pro cache css a js
		$this->addToExistDirs(WWW_DIR . "/cache/");
		$this->addToExistDirs(WWW_DIR . "/cache/js/");
		$this->addToExistDirs(WWW_DIR . "/cache/css/");
	}

	/**
	 * Zkontroluje, zda existují všechny složky.
	 */
	private function checkDirs() {
		foreach ($this->dirs as $dir) {
			if (!file_exists($dir["path"])) {
				mkdir($dir["path"]);
				echo "složka " . $dir["path"] . " BYLA VYTVOŘENA <br />";
			} else {
				echo "složka " . $dir["path"] . " již existuje <br />";
			}
		}
	}

	/**
	 * Metoda soužící pro přidání dalšího adresáře do adresářů, které by měli
	 * existovat. Prosím používejte k přidání vždy tuto metodu.
	 * @param string $path cesta k adresáři který by měl existovat
	 */
	private function addToExistDirs($path) {
		$this->dirs[] = array(
			"path" => $path
		);
	}

}
