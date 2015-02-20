<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace AdminModule;

use App\Forms as Frm;
use POS\Model\UserPropertyDao;
use Nette\DateTime;
use POSComponent\Graph;
use Nette\Forms\Container;

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

	public function startup() {
		parent::startup();
		Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
			return $container[$name] = new \NetteExt\Picker\DatePicker($label);
		});
	}

	public function renderDefault() {
		$this->template->countMan = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_MAN)->count();
		$this->template->countWoman = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_WOMAN)->count();
		$this->template->countCouple = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE)->count();
		$this->template->countCoupleMen = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE_MAN)->count();
		$this->template->countCoupleWomen = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_COUPLE_WOMAN)->count();
		$this->template->countGroup = $this->userPropertyDao->getByType(UserPropertyDao::PROPERTY_GROUP)->count();

		$this->template->totalCount = $this->userPropertyDao->getAll()->count();
	}

	protected function createComponentDailyGraph($name) {

		$this->statisticManager->setUserDao($this->userDao);
		$regStats = $this->statisticManager->getRegUsers();

		$graphComponent = new Graph($regStats, $this, $name);

		return $graphComponent;
	}

}
