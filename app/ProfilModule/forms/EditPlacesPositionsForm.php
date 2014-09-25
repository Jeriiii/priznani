<?php

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use Nette\Application\UI\Form as Frm;
use POS\Model\UserPositionDao;
use Nette\ComponentModel\IContainer;
use POS\Model\EnumPositionDao;
use POS\Model\UserPlaceDao;
use POS\Model\UserDao;
use POS\Model\EnumPlaceDao;

class EditPlacesPositionsForm extends PlacesAndPositionsForm {

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

		$this->enumPositionDao = $enumPositionDao;
		$this->userPositionDao = $userPositionDao;
		$this->userPlaceDao = $userPlaceDao;
		$this->enumPlaceDao = $enumPlaceDao;
		$this->userDao = $userDao;

		parent::__construct($userPositionDao, $enumPositionDao, $userPlaceDao, $enumPlaceDao, $userDao, $parent, $name);

		$userProperty = $this->userDao->findProperties($this->presenter->user->id);
		$placeValues = $this->userPlaceDao->getFilled($userProperty->id);

		foreach ($placeValues as $value) {
			$sel = $this->enumPlaceDao->getFilledPlaces($value->enum_placeID);

			if ($sel->place == $this->bed->caption) {
				$this->bed->setDefaultValue(true);
			}
			if ($sel->place == $this->car->caption) {
				$this->car->setDefaultValue(true);
			}
			if ($sel->place == $this->nature->caption) {
				$this->nature->setDefaultValue(true);
			}
			if ($sel->place == $this->unusual->caption) {
				$this->unusual->setDefaultValue(true);
			}
			if ($sel->place == $this->public->caption) {
				$this->public->setDefaultValue(true);
			}
		}

		$positionValues = $this->userPositionDao->getFilled($userProperty->id);

		foreach ($positionValues as $value) {
			$sel = $this->enumPositionDao->getFilledPositions($value->enum_positionID);

			if ($sel->position == $this->fromBack->caption) {
				$this->fromBack->setDefaultValue(true);
			}
			if ($sel->position == $this->position69->caption) {
				$this->position69->setDefaultValue(true);
			}
			if ($sel->position == $this->riding->caption) {
				$this->riding->setDefaultValue(true);
			}
			if ($sel->position == $this->side->caption) {
				$this->side->setDefaultValue(true);
			}
			if ($sel->position == $this->missionary->caption) {
				$this->missionary->setDefaultValue(true);
			}
		}
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$userProperty = $this->userDao->findProperties($this->presenter->user->id);

		$positionsArray = array($this->fromBack, $this->position69, $this->riding, $this->side, $this->missionary);

		$placesArray = array($this->bed, $this->car, $this->nature, $this->unusual, $this->public);

		foreach ($positionsArray as $position) {
			$this->setPositionValues($values, $position->caption, $userProperty, $position);
		}

		foreach ($placesArray as $place) {
			$this->setPlaceValues($values, $place->caption, $userProperty, $place);
		}

		$presenter = $this->getPresenter();
		$presenter->flashMessage('message');
		$presenter->redirect('this');
	}

	/* maže nebo vytváří záznam pozice podle zaškrtnutých polí */

	public function setPositionValues($values, $posName, $userProperty, $position) {

		$sel = $this->enumPositionDao->selPosition($posName);
		$values->position = $position;

		if ($values->position->value == TRUE) {
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		} else {
			$this->userPositionDao->deleteSelPosition($userProperty->id, $sel->id);
		}
	}

	/* maže nebo updatuje záznam oblíbených míst podle zaškrtnutých polí */

	public function setPlaceValues($values, $placeName, $userProperty, $place) {

		$sel = $this->enumPlaceDao->selPlace($placeName);
		$values->place = $place;

		if ($values->place->value == TRUE) {
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		} else {
			$this->userPlaceDao->deleteSelPlace($userProperty->id, $sel->id);
		}
	}

}
