<?php

/**
 * Slouží pro instalaci ostatních složek, které git necomituje.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use NetteExt\Install\DB\InstallDB;
use NetteExt\Install\DirChecker;
use NetteExt\Install\Messages;
use NetteExt\Install\ClearCasch;

class InstallPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\DatabaseDao
	 * @inject
	 */
	public $dbDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

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
		$dirCheker->addUsers($this->userDao);
		$dirCheker->check();

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		/* obnoví kompletně celou DB pos i postest */
		$instalDB = new InstallDB($this->dbDao, $messages);
		$instalDB->installAll();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionClearCache() {
	$messages = new Messages;

	$clearCache = new ClearCasch($messages);
	$clearCache->clearCache();

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
