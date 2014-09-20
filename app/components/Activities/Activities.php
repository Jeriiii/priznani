<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

use POSComponent\BaseProjectControl;
use POS\Model\ActivitiesDao;
use Nette\Application\Responses\JsonResponse;

/**
 * Komponenta pro vykreslení aktivit uživatele.
 *
 * @author Daniel Holubář
 */
class Activities extends BaseProjectControl {

	/**
	 * @var \POS\Model\ActivitiesDao
	 * @inject
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
	 * Obsluha pro načtení aktivit, posílá JSON s polem textů
	 */
	public function handleLoadActivities() {
		$activities = $this->getUserActivities($this->userID);
		$activityObj = new Activity();

		foreach ($activities as $item) {
			if ($item->statusID != NULL) {
				$data[] = $activityObj->getUserStatusAction($item->event_creator->user_name, $item->event_type, $item->status->text, $item->id, $item->viewed);
			} elseif ($item->imageID != NULL) {
				$data[] = $activityObj->getUserImageAction($item->event_creator->user_name, $item->event_type, $item->image, $item->id, $item->viewed);
			} else {
				$data[] = $activityObj->getUserAction($item->event_creator->user_name, $item->event_type, $item->id, $item->viewed);
			}
		}

		//pokud nejsou žádné aktivity pošleme 0
		if (!isset($data)) {
			$this->presenter->sendResponse(new JsonResponse(array("activities" => 0)));
		} else {
			$this->presenter->sendResponse(new JsonResponse(array("activities" => $data)));
		}
		$this->redrawControl();
	}

	/**
	 * Signál na počet nových aktivit, pošle JSON odpověď
	 */
	public function handleAsk() {
		$count = $this->getUnviewedActivitiesCount($this->userID);
		$this->presenter->sendResponse(new JsonResponse(array("count" => $count)));
		$this->redrawControl();
	}

	/**
	 * Označí aktivitu jako přečtenou bez invalidace komponenty
	 * @param int $activityID ID aktivity
	 */
	public function handleViewed($activityID) {
		$this->activitiesDao->markViewed($activityID);
		$this->redrawControl();
	}

	/**
	 * označí všechny aktivity daného usera za přečtené
	 */
	public function handleAllViewed() {
		$this->activitiesDao->markAllViewed($this->userID);
		$this->redrawControl();
	}

}
