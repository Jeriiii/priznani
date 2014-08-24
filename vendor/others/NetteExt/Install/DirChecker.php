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
use NetteExt\Install\Messages;

class DirChecker extends \Nette\Object {
	/* adresáře, které by měli existovat */

	private $dirs = array();

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao = NULL;

	/** @var Messages */
	private $messages;

	public function __construct($messages = NULL) {
		if (isset($messages)) {
			$this->messages = $messages;
		} else {
			$this->messages = new Messages;
		}
	}

	/**
	 * Zkontroluje zda složky existují. Pokud ne, vytvoří je.
	 */
	public function check() {
		// přidá statické složky ke zkontrolování - ty co se musí vytvořit vždy
		$this->addStaticDirs();
		// přidá složky závislé na uživateli
		if (isset($this->userDao)) {
			$this->addUserDirs();
		}

		//zkontroluje složky
		$this->checkDirs();
	}

	/**
	 * Přidá kontrolu i uživatelských složek.
	 * @param \POS\Model\UserDao $userDao
	 */
	public function addUsers($userDao) {
		$this->userDao = $userDao;
	}

	/**
	 * Přidá složky závislé na uživatelích
	 */
	public function addUserDirs() {
		$users = $this->userDao->getAll();
		$basePath = WWW_DIR . "/images/userGalleries/";
		foreach ($users as $user) {
			$this->addToExistDirs($basePath . $user->id . "/");
		}
	}

	/**
	 * Přidá složky ke zkontrolování.
	 */
	private function addStaticDirs() {
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
				$this->messages->addMessage("složka " . $dir["path"] . " BYLA VYTVOŘENA");
			} else {
				$this->messages->addMessage("složka " . $dir["path"] . " již existuje");
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
