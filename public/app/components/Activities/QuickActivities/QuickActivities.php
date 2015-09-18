<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Activity;

use POSComponent\BaseProjectControl;
use POS\Model\ActivitiesDao;
use Nette\Application\Responses\JsonResponse;
use Nette\Database\Table\ActiveRow;
use Nette\ArrayHash;
use POS\Model\PaymentDao;

/**
 * Komponenta pro vykreslení rychlého přehledu aktivit všech uživatelů.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class QuickActivities extends \POSComponent\UsersList\AjaxList {

	/** @var \POS\Model\ActivitiesDao @inject */
	public $activitiesDao;

	/* ID vlastníka aktivit */
	protected $userID;

	/** @var Selection Aktivity uřivatele. */
	private $activities;

	/**
	 * Proměnná s uživatelskými daty (cachovaný řádek z tabulky users). Obsahuje relace na profilFoto, gallery, property
	 * @var ArrayHash|ActiveRow řádek z tabulky users
	 */
	protected $loggedUser;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	public function __construct(ActivitiesDao $activitiesDao, $loggedUser, PaymentDao $paymentDao) {
		parent::__construct();
		if (!($loggedUser instanceof ActiveRow) && !($loggedUser instanceof ArrayHash)) {
			throw new Exception("variable loggedUser must by instance of ActiveRow or ArrayHash");
		}
		$this->limit = 4;
		$this->userID = $loggedUser->id;
		$this->activitiesDao = $activitiesDao;
		$this->loggedUser = $loggedUser;
		$this->paymentDao = $paymentDao;
	}

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		parent::render();
		$template = $this->template;
		$this->template->activities = $this->activities;
		$this->template->loggedUser = $this->loggedUser;
		$this->template->userIsPaying = $this->paymentDao->isUserPaying($this->userID);
		$template->setFile(dirname(__FILE__) . '/quickActivities.latte');
		$template->render();
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

	public function getSnippetName() {
		return "list";
	}

	public function setData($offset) {
		$activities = $this->activitiesDao->getByUserId($this->userID, $this->limit, $offset);
		$this->activities = $activities;
	}

}
