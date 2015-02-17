<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace AdminModule;

use Nette;
use App\Forms as Frm;
use POS\Model\UserPropertyDao;
use Nette\DateTime;
use POSComponent\GraphComponent;

/**
 * Adminstrace statistik
 *
 * @author Petr Kukrál
 */
class StatisticPresenter extends AdminSpacePresenter {

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Statistics\StatisticManager @inject */
	public $statisticManager;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	public function renderDefault() {
		$this->template->countMan = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_MAN)->count();
		$this->template->countWoman = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_WOMAN)->count();
		$this->template->countCouple = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE)->count();
		$this->template->countCoupleMen = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE_MAN)->count();
		$this->template->countCoupleWomen = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE_WOMAN)->count();
		$this->template->countGroup = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_GROUP)->count();

		$this->template->totalCount = $this->userPropertyDao->getAll()->count();
	}

	protected function createComponentGraph($name) {
		$countDays = 7;
		$fromDate = new DateTime();
		$fromDate->modify("- $countDays days");
		$dailyRegistrations = $this->statisticManager->getRegUsersByDay($this->userDao, $fromDate, $countDays);

		$fromDate->modify('+ 1 days'); //korekce posunu dnů

		$graphComponent = new GraphComponent($dailyRegistrations, $fromDate, $this, $name);

		return $graphComponent;
	}

}
