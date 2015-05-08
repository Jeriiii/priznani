<?php

namespace SafeModule;

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
use POS\Model\UserPropertyDao;
use NetteExt\DBMover\DBMover;

class InstallPresenter extends BasePresenter {

	/** @var \POS\Model\DatabaseDao @inject */
	public $dbDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\CatPropertyWantToMeetDao @inject */
	public $catPropertyWantToMeetDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	/** @var \POS\Model\StreamDao @inject */
	public $streamDao;

	/** @var \POS\Model\ConfessionDao @inject */
	public $confessionDao;

	/**
	 * Vloží všechny kombinace kategorií
	 */
	public function actionAllCategories() {
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$this->insertEnumCatProp();
		$this->insertUserCategories();

		$messages->addMessage("Kategorie byly úspěšně vloženy.");
		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionAll() {
		ini_set('max_execution_time', 300);
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
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionDBStruct() {
		ini_set('max_execution_time', 300);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		/* obnoví kompletně celou DB pos i postest */
		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->installDBStruct();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionMoveDb() {
		ini_set('max_execution_time', 600);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$dbMover = new DBMover($this->dbDao);
		//$dbMover->saveToCache();
		$messages->addMessage('Data byla uložena do DB');

		$dbMover->loadFromCache();
		$messages->addMessage('Data byla načtena z DB');

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionClearCache() {
		$messages = new Messages;

		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionTestData() {
		ini_set('max_execution_time', 60);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataTestDb();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionData() {
		ini_set('max_execution_time', 60);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionAllData() {
		ini_set('max_execution_time', 90);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataDb();
		$instalDB->dataTestDb();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionMoreData() {
		ini_set('max_execution_time', 90);
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->dataDbMore();

		$messages->flash($this);
		$clearCache->clearCache();
		$this->redirect("Install:");
	}

	public function actionCreateDBPatch() {
		$messages = new Messages;

		/* vyčistí cache */
		$clearCache = new ClearCasch($messages);
		$clearCache->clearCache();

		$instalDB = new InstallDB($this->dbDao, $this->testMode, $messages);
		$instalDB->createPath();

		$messages->flash($this);
		$clearCache->clearCache();
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

			if ($row["want_to_meet_group"] == 2 && $row["want_to_meet_couple_women"] == 2 && $row["want_to_meet_couple_men"] == 2 && $row["want_to_meet_couple"] == 2 && $row["want_to_meet_women"] == 2 && $row["want_to_meet_men"] == 2) {
				break;
			}

			$row["want_to_meet_group"] ++;
		}
		//}

		$this->catPropertyWantToMeetDao->begginTransaction();
		$this->catPropertyWantToMeetDao->deleteAllTable();
		foreach ($properties as $property) {
			foreach ($insert as $row) {
				$row["type"] = $property;
				$this->catPropertyWantToMeetDao->insert($row);
			}
		}
		$this->catPropertyWantToMeetDao->endTransaction();
	}

	/**
	 * Speciální metoda vkládá všechny kombinace kategorií
	 */
	private function insertUserCategories() {
		$catsWTMP = $this->catPropertyWantToMeetDao->getAll();
		$ids = $catsWTMP->fetchPairs("id", "id");

		$this->userCategoryDao->begginTransaction();
		$this->userCategoryDao->deleteAllTable();
		foreach ($ids as $id) {
			$this->userCategoryDao->insert(array("property_want_to_meet" => $id));
		}
		$this->userCategoryDao->endTransaction();
	}

	/**
	 * Přepočítá u všech uživatelů kategorie, do kterých spadají.
	 */
	public function recalculateUserCategories() {
		$users = $this->userDao->getAll();
		foreach ($users as $user) {
			$property = $user->property;

			if (!empty($property)) {
				$categoryID = $this->userCategoryDao->getMyCategory($property)->id;
				$this->userPropertyDao->update($property->id, array(
					UserPropertyDao::COLUMN_PREFERENCES_ID => $categoryID
				));

				/* přepočítá i výsledky ve streamu */
				$this->streamDao->updateCatByUser($user->id, $categoryID);
			}
		}
	}

}
