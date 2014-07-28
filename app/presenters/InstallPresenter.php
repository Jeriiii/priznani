<?php

/**
 * Slouží pro instalaci ostatních složek, které git necomituje.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Utils\Finder;
use NetteExt\Install\DB\InstallDB;
use SQLParser\PHPSQLParser;
use Nette\Database;
use Nette\Diagnostics\Debugger;
use Nette\Environment;
use NetteExt\Install\DirChecker;

class InstallPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\DatabaseDao
	 * @inject
	 */
	public $dbDao;

	public function actionDefault() {
		ini_set('max_execution_time', 300);

		/* zkontroluje zda existují složky */
		$dirCheker = new DirChecker();
		$dirCheker->check();

		/* obnoví kompletně celou DB pos i postest */
		$instalDB = new InstallDB($this->dbDao);
		$instalDB->installAll();

		die();
	}

	public function actionTestData() {
		$instalDB = new InstallDB($this->dbDao);
		$instalDB->dataPostestDb();

		die();
	}

}
