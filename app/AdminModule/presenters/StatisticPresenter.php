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
use POSComponent\Lines;

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

	protected function createComponentRegistrationGraph($name) {
		$this->statisticManager->setUserDao($this->userDao);
		$regStats = $this->statisticManager->getRegUsers();

		$graph = new Graph($this, $name);
		$graph->graphName = 'Statistika registrací';
		$graph->addLine($regStats, 'Počet registrací');

		return $graph;
	}

	protected function createComponentPeopleBySexGraph($name) {
		$this->statisticManager->setUserDao($this->userDao);
		$sexStats = $this->statisticManager->getPeopleBySex();

		$graph = new Graph($this, $name);
		$graph->graphName = 'Statistika zastoupení skupin';
		$graph->addLine($sexStats, 'Počet lidí ve skupině');
		$graph->setTypePie();

		return $graph;
	}

	protected function createComponentPeopleByAgeGraph($name) {
		$this->statisticManager->setUserDao($this->userDao);
		$ageStats = $this->statisticManager->getPeopleByAge();

		$graph = new Graph($this, $name);
		$graph->graphName = 'Statistika podle věku';
		$graph->addLine($ageStats, 'Počet lidí');
		$graph->setTypePie();

		return $graph;
	}

}
