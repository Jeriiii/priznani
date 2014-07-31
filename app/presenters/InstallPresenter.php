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
use NetteExt\Install\Messages;

class InstallPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\DatabaseDao
	 * @inject
	 */
	public $dbDao;

	public function startup() {
		parent::startup();
		// ochrana proti spuštění instalace na ostrém serveru
		if ($this->productionMode) {
			$this->redirect("OnePage:");
		}

		$this->setLayout("layoutInstall");
	}

	public function actionAll() {
		ini_set('max_execution_time', 300);
		$messages = new Messages;

		/* zkontroluje zda existují složky */
		$dirCheker = new DirChecker($messages);
		$dirCheker->check();

		/* obnoví kompletně celou DB pos i postest */
		$instalDB = new InstallDB($this->dbDao, $messages);
		$instalDB->installAll();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionTestData() {
		$messages = new Messages;

		$instalDB = new InstallDB($this->dbDao, $messages);
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionData() {
		$messages = new Messages;

		$instalDB = new InstallDB($this->dbDao, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionAllData() {
		$messages = new Messages;

		$instalDB = new InstallDB($this->dbDao, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

}
