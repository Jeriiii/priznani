<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use POSComponent\BaseProjectControl;
use POS\Model\ActivitiesDao;

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

}
