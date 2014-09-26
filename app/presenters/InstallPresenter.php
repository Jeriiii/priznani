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

	/**
	 * @var \POS\Model\CatPropertyWantToMeetDao
	 * @inject
	 */
	public $catPropertyWantToMeetDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\UserCategoryDao
	 * @inject
	 */
	public $userCategoryDao;

	public function startup() {
		parent::startup();
		// ochrana proti spuštění instalace na ostrém serveru
		if ($this->productionMode) {
			$this->redirect("OnePage:");
		}

		//$this->insertEnumCatProp();
		$this->insertUserCategories();

		$this->setLayout("layoutInstall");
	}

	public function actionAll() {
		ini_set('max_execution_time', 400);
		$messages = new Messages;

		/* zkontroluje zda existují složky */
		$dirCheker = new DirChecker($messages);

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		/* obnoví kompletně celou DB pos i postest */
		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->installAll();

		/* zkontroluje, zda existují uživateslké složky */
		$dirCheker->addUsers($this->userDao);
		$dirCheker->check();

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

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionData() {
		$messages = new Messages;

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	public function actionAllData() {
		$messages = new Messages;

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$this->redirect("Install:");
	}

	/**
	 * Speciální metoda vkládající všechny kombinace do tab. s kategoriemi
	 * na tabulku want to meet a property
	 */
	public function insertEnumCatProp() {
		$properties = array(1, 2, 3, 4, 5, 6);
		$insert = array();
		$row = array(
			"want_to_meet_men" => 0,
			"want_to_meet_women" => 0,
			"want_to_meet_couple" => 0,
			"want_to_meet_couple_men" => 0,
			"want_to_meet_couple_women" => 0,
			"want_to_meet_group" => 0,
			"type" => 0
		);

		//foreach ($properties as $property) {
		while (1) {
			if ($row["want_to_meet_group"] == 3) {
				$row["want_to_meet_group"] = 0;
				$row["want_to_meet_couple_women"] ++;
			}

			//$row["want_to_meet_couple_women"] ++;
			if ($row["want_to_meet_couple_women"] == 3) {
				$row["want_to_meet_couple_women"] = 0;
				$row["want_to_meet_couple_men"] ++;
			}

			//$row["want_to_meet_couple_men"] ++;
			if ($row["want_to_meet_couple_men"] == 3) {
				$row["want_to_meet_couple_men"] = 0;
				$row["want_to_meet_couple"] ++;
			}

			//$row["want_to_meet_couple"] ++;
			if ($row["want_to_meet_couple"] == 3) {
				$row["want_to_meet_couple"] = 0;
				$row["want_to_meet_women"] ++;
			}

			//$row["want_to_meet_women"] ++;
			if ($row["want_to_meet_women"] == 3) {
				$row["want_to_meet_women"] = 0;
				$row["want_to_meet_men"] ++;
			}

			//$row["want_to_meet_men"] ++;
			if ($row["want_to_meet_men"] == 3) {
				$row["want_to_meet_couple_women"] = 0;
				// preteklo, nic se nestane
			}

			//$row["type"] = $property;
			$insert[] = $row;
			$row["want_to_meet_group"] ++;

			if ($row["want_to_meet_group"] == 1 && $row["want_to_meet_couple_women"] == 1 && $row["want_to_meet_couple_men"] == 1 && $row["want_to_meet_couple"] == 1 && $row["want_to_meet_women"] == 1 && $row["want_to_meet_men"] == 1) {
				break;
			}
		}
		//}

		$this->catPropertyWantToMeetDao->begginTransaction();
		foreach ($properties as $property) {
			foreach ($insert as $row) {
				$row["type"] = $property;
				$this->catPropertyWantToMeetDao->insert($row);
			}
		}
		$this->catPropertyWantToMeetDao->endTransaction();
	}

	private function insertUserCategories() {
		$catsWTMP = $this->catPropertyWantToMeetDao->getAll();
		$ids = $catsWTMP->fetchPairs("id", "id");

		$this->userCategoryDao->begginTransaction();
		foreach ($ids as $id) {
			$this->userCategoryDao->insert(array("property_want_to_meet" => $id));
		}
		$this->userCategoryDao->endTransaction();
	}

}
