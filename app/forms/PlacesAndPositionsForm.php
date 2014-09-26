<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use POS\Model\UserPositionDao;
use Nette\ComponentModel\IContainer;
use POS\Model\EnumPositionDao;
use POS\Model\UserPlaceDao;
use POS\Model\UserDao;
use POS\Model\EnumPlaceDao;

/**
 * Formulář pro vyplnění oblíbeného místa k milování a oblíbené polohy
 */
class PlacesAndPositionsForm extends BaseForm {

	/**
	 * @var \POS\Model\UserPositionDao
	 */
	public $userPositionDao;

	/**
	 * @var \POS\Model\EnumPositionDao
	 */
	public $enumPositionDao;

	/**
	 * @var \POS\Model\UserPlaceDao
	 */
	public $userPlaceDao;

	/**
	 * @var \POS\Model\EnumPlaceDao
	 */
	public $enumPlaceDao;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;
	public $fromBack;
	public $position69;
	public $riding;
	public $side;
	public $missionary;
	public $bed;
	public $car;
	public $nature;
	public $unusual;
	public $public;

	public function __construct(UserPositionDao $userPositionDao, EnumPositionDao $enumPositionDao, UserPlaceDao $userPlaceDao, EnumPlaceDao $enumPlaceDao, Userdao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->enumPositionDao = $enumPositionDao;
		$this->userPositionDao = $userPositionDao;
		$this->userPlaceDao = $userPlaceDao;
		$this->enumPlaceDao = $enumPlaceDao;
		$this->userDao = $userDao;
		$this->addGroup('Oblíbené polohy při milování');
		$this->fromBack = $this->addCheckbox('fromBack', 'zezadu');
		$this->position69 = $this->addCheckbox('position69', '69');
		$this->missionary = $this->addCheckbox('missionary', 'misionář');
		$this->riding = $this->addCheckbox('riding', 'na koníčka');
		$this->side = $this->addCheckbox('side', 'na boku');
		$this->addGroup('Oblíbená místa při milování');
		$this->bed = $this->addCheckbox('bed', 'postel');
		$this->car = $this->addCheckbox('car', 'auto');
		$this->nature = $this->addCheckbox('nature', 'příroda');
		$this->unusual = $this->addCheckbox('unusual', 'neobvyklé místo');
		$this->public = $this->addCheckbox('public', 'na veřejnosti');

		$this->addSubmit('send', 'Odeslat');
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$userProperty = $this->userDao->findProperties($presenter->user->id);

		/* pozice */
		if ($values->fromBack) {
			$sel = $this->enumPositionDao->selPosition('zezadu');
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		}
		if ($values->position69) {
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		}
		if ($values->missionary) {
			$sel = $this->enumPositionDao->selPosition('misionář');
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		}
		if ($values->riding) {
			$sel = $this->enumPositionDao->selPosition('na koníčka');
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		}
		if ($values->side) {
			$sel = $this->enumPositionDao->selPosition('na boku');
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		}
		/* místa */
		if ($values->bed) {
			$sel = $this->enumPlaceDao->selPlace('postel');
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		}
		if ($values->car) {
			$sel = $this->enumPlaceDao->selPlace('auto');
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		}
		if ($values->nature) {
			$sel = $this->enumPlaceDao->selPlace('příroda');
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		}
		if ($values->unusual) {
			$sel = $this->enumPlaceDao->selPlace('neobvyklé místo');
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		}
		if ($values->public) {
			$sel = $this->enumPlaceDao->selPlace('na veřejnosti');
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		}

		$this->getPresenter()->flashMessage('Vaše údaje byly uloženy.');
		$this->getPresenter()->redirect('this');
	}

}
