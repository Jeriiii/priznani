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

	public function __construct(UserPositionDao $userPositionDao, EnumPositionDao $enumPositionDao, UserPlaceDao $userPlaceDao, EnumPlaceDao $enumPlaceDao, Userdao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);



		$this->enumPositionDao = $enumPositionDao;
		$this->userPositionDao = $userPositionDao;
		$this->userPlaceDao = $userPlaceDao;
		$this->enumPlaceDao = $enumPlaceDao;
		$this->userDao = $userDao;
		$this->addCheckboxList('positions', 'Oblíbené polohy při milování:', $enumPositionDao->getAll()->fetchPairs(
				EnumPositionDao::COLUMN_ID, EnumPositionDao::COLUMN_POSITION
		));

		$this->addCheckboxList('places', 'Oblíbená místa při milování:', $enumPlaceDao->getAll()->fetchPairs(
				EnumPlaceDao::COLUMN_ID, EnumPlaceDao::COLUMN_PLACE
		));

		$this->addSubmit('send', 'Odeslat');
		$this->setInputContainer(FALSE);
		$this->setBootstrapRender();
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$userProperty = $this->userDao->findProperties($presenter->user->id);
		/* pozice */
		foreach ($values->positions as $positionID) {
			$this->userPositionDao->insertNewPosition($userProperty->id, $positionID);
		}
		/* místa */
		foreach ($values->places as $placeID) {
			$this->userPlaceDao->insertNewPlace($userProperty->id, $placeID);
		}

		$this->getPresenter()->flashMessage('Vaše údaje byly uloženy.');
		$this->getPresenter()->redirect('this');
	}

}
