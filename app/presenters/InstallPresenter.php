<?php

/**
 * Slouží pro instalaci ostatních složek, které git necomituje.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Utils\Finder;
use NetteExt\Install\InstallDB;
use SQLParser\PHPSQLParser;
use Nette\Database;
use Nette\Diagnostics\Debugger;
use Nette\Environment;

class InstallPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\DatabaseDao
	 * @inject
	 */
	public $dbDao;



	/* adresáře, které by měli existovat */
	private $dirs = array();

	public function actionDefault() {
		ini_set('max_execution_time', 300);

		// přidání složek pro galerie
		$this->addToExistDirs(WWW_DIR . "/images/galleries/");
		$this->addToExistDirs(WWW_DIR . "/images/userGalleries/");
		$this->addToExistDirs(WWW_DIR . "/images/users/profils/");

		// přidání složek pro cache css a js
		$this->addToExistDirs(WWW_DIR . "/cache/");
		$this->addToExistDirs(WWW_DIR . "/cache/js/");
		$this->addToExistDirs(WWW_DIR . "/cache/css/");

		$this->controlDirs();

		InstallDB::sqlInstall($this->dbDao->getDatabase());
		die();
	}

	/**
	 * zkontroluje, zda existují všechny složky
	 */
	private function controlDirs() {
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
	 * @param type $path cesta k adresáři který by měl existovat
	 */
	private function addToExistDirs($path) {
		$this->dirs[] = array(
			"path" => $path
		);
	}

	public function renderDefault() {

	}

}
