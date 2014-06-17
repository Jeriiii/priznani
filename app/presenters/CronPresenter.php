<?php

/**
 * Vydává schválená přiznání.
 */
class CronPresenter extends BasePresenter {

	private $dataToDebug;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\StreamDao
	 * @inject
	 */
	public $streamDao;

	/**
	 * @var \POS\Model\AdviceDao
	 * @inject
	 */
	public $adviceDao;

	/**
	 * @var \POS\Model\PartyDao
	 * @inject
	 */
	public $partyDao;

	public function startup() {
		parent::startup();

		$this->setLayout("simpleLayout");
	}

	public function actionReleaseConfessions() {
//		$allConCount = $this->confessionDao->countReleaseNotStream();

		$limit = 15;
		$confessions = $this->confessionDao->getToRelease($limit);

		$now = new \Nette\DateTime();

		$counter = 1;

		$this->confessionDao->begginTransaction();

		foreach ($confessions as $con) {
			$this->streamDao->addNewConfession($con->id, $now);
			$counter ++;
		}

		$this->confessionDao->setAsRelease($confessions);

		$this->confessionDao->endTransaction();

//		echo("Nahrávání proběhlo úspěšně, bylo nahráno " . --$counter . " přiznání z " . $allConCount);
		die();
//		$this->redirect("this");
	}

	public function actionWow() {
		$pripojeni_manius['ip'] = '91.219.244.178';
		$pripojeni_manius['uzivatel'] = 'priznani';
		$pripojeni_manius['heslo'] = 'SJ28XNypF';
		$pripojeni_manius['db'] = 'priznani';
		$spojeni_manius = mysql_connect($pripojeni_manius['ip'], $pripojeni_manius['uzivatel'], $pripojeni_manius['heslo']);
		mysql_select_db($pripojeni_manius['db'], $spojeni_manius);
		mysql_set_charset('utf8', $spojeni_manius);

		$now = new DateTime();
		$morning = $now->setTime(0, 0, 0)->modify('-1 day');
		$now2 = new DateTime();
		$night = $now2->setTime(23, 59, 59)->modify('-1 day');

		$partyConfessions = $this->partyDao->getBetweenRelease($morning, $night);

		$query = "";

		foreach ($partyConfessions as $confession) {
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznanizparby (text, datum) VALUES ('" . addslashes($confession->note) . "', '" . $release_date . "');";
			mysql_query($insert, $spojeni_manius);
			$query = $query . $insert;
		}

		$sexConfessions = $this->confessionDao->getBetweenRelease($morning, $night);

		$query2 = "";

		foreach ($sexConfessions as $confession) {
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznaniosexu (text, datum) VALUES ('" . addslashes($confession->note) . "', '" . $release_date . "');";
			mysql_query($insert, $spojeni_manius);
			$query2 = $query2 . $insert;
		}
		$this->dataToDebug = $query;
		//mysql_query($query, $spojeni_manius);
		mysql_close($spojeni_manius);
	}

	public function renderWow() {
		$this->template->dataToDebug = $this->dataToDebug;
	}

}
