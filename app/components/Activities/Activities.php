<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use POSComponent\BaseProjectControl;
use POS\Model\ActivitiesDao;
use Nette\Application\Responses\JsonResponse;

/**
 * Komponenta pro vykreslení aktivit uživatele.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Activities extends BaseProjectControl {

	/**
	 * @var \POS\Model\ActivitiesDao
	 */
	public $activitiesDao;

	/*
	 * ID vlastníka aktivit
	 */
	protected $userID;

	/**
	 * Indikuje otevřené/zavřené okno
	 */
	protected $load = FALSE;

	/**
	 *
	 * @param int $userID Id uživatele, který vlastní aktivitu
	 * @param \POS\Model\ActivitiesDao $activitiesDao
	 */
	public function __construct($userID, ActivitiesDao $activitiesDao) {
		parent::__construct();
		$this->userID = $userID;
		$this->activitiesDao = $activitiesDao;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		$template = $this->template;
		$template->load = $this->load;
		$template->setFile(dirname(__FILE__) . '/activities.latte');
		$template->activities = $this->getUserActivities($this->userID);
		// Objekt pro vybrání a složení správného textu
		$template->activityObj = new Activity();
		$template->render();
	}

	/**
	 * Získání aktivit uživatele
	 * @param int $userID Id uživatele, jehož aktivity chceme získat
	 * @return Nette\Database\Table\Selection
	 */
	protected function getUserActivities($userID) {
		$userActivities = $this->activitiesDao->getActivitiesByUserId($userID);
		return $userActivities;
	}

	/**
	 * 	Získání počtu nepřečtených aktivit
	 * @param int $userID ID vlastníka aktivity
	 * @return int
	 */
	protected function getUnviewedActivitiesCount($userID) {
		$count = $this->activitiesDao->getCountOfUnviewed($userID);
		return $count;
	}

	/**
	 * Obsluha pro načtení aktivit
	 */
	public function handleLoadActivities() {
		$this->load = TRUE;
		$this->redrawControl();
	}

	/**
	 * Signál na počet nových aktivit, pošle JSON odpověď
	 */
	public function handleAsk() {
		$count = $this->getUnviewedActivitiesCount($this->userID);
		$this->presenter->sendResponse(new JsonResponse(array("count" => $count)));
	}

	/**
	 * Označí aktivitu jako přečtenou bez invalidace komponenty
	 * @param int $activityID ID aktivity
	 */
	public function handleViewed($activityID) {
		$this->activitiesDao->markViewed($activityID);
	}

	/**
	 * označí všechny aktivity daného usera za přečtené
	 */
	public function handleAllViewed() {
		$this->activitiesDao->markAllViewed($this->userID);

		$this->load = TRUE;
		$this->redrawControl();
	}

}
