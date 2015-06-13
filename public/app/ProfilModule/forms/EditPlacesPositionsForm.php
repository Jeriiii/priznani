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

	/** @var \POS\Model\UserPositionDao */
	public $userPositionDao;

	/** @var \POS\Model\EnumPositionDao */
	public $enumPositionDao;

	/** @var \POS\Model\UserPlaceDao */
	public $userPlaceDao;

	/** @var \POS\Model\EnumPlaceDao */
	public $enumPlaceDao;

	/** @var \POS\Model\UserDao */
	public $userDao;

	public function __construct(UserPositionDao $userPositionDao, EnumPositionDao $enumPositionDao, UserPlaceDao $userPlaceDao, EnumPlaceDao $enumPlaceDao, Userdao $userDao, IContainer $parent = NULL, $name = NULL) {

		$this->enumPositionDao = $enumPositionDao;
		$this->userPositionDao = $userPositionDao;
		$this->userPlaceDao = $userPlaceDao;
		$this->enumPlaceDao = $enumPlaceDao;
		$this->userDao = $userDao;

		parent::__construct($userPositionDao, $enumPositionDao, $userPlaceDao, $enumPlaceDao, $userDao, $parent, $name);

		$userProperty = $this->userDao->findProperties($this->presenter->user->id);
		$places = $this->userPlaceDao->getFilled($userProperty->id)->fetchPairs(
			UserPlaceDao::COLUMN_ENUM_PLACE_ID, UserPlaceDao::COLUMN_ENUM_PLACE_ID
		);

		$this->setDefaults(array("places" => $places));

		$positions = $this->userPositionDao->getFilled($userProperty->id)->fetchPairs(
			UserPositionDao::COLUMN_USER_ENUM_POSITION_ID, UserPositionDao::COLUMN_USER_ENUM_POSITION_ID
		);

		$this->setDefaults(array("positions" => $positions));

		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		$userProperty = $this->userDao->findProperties($this->presenter->user->id);

		$this->userPositionDao->begginTransaction();

		/* místa */
		$this->userPlaceDao->deleteByProperty($userProperty->id);
		foreach ($values->places as $placeID) {
			$this->userPlaceDao->insertNewPlace($userProperty->id, $placeID);
		}
		/* pozice */
		$this->userPositionDao->deleteByProperty($userProperty->id);
		foreach ($values->positions as $positionID) {
			$this->userPositionDao->insertNewPosition($userProperty->id, $positionID);
		}

		$this->userPositionDao->endTransaction();

		$presenter->flashMessage('Údaje byly změněny.');
		$presenter->redirect('this');
	}

	/* maže nebo vytváří záznam pozice podle zaškrtnutých polí */

	public function setPositionValues($values, $posName, $userProperty, $position) {

		$sel = $this->enumPositionDao->findByName($posName);
		$values->position = $position;

		if ($values->position->value == TRUE) {
			$this->userPositionDao->insertNewPosition($userProperty->id, $sel->id);
		} else {
			$this->userPositionDao->deleteSelPosition($userProperty->id, $sel->id);
		}
	}

	/* maže nebo updatuje záznam oblíbených míst podle zaškrtnutých polí */

	public function setPlaceValues($values, $placeName, $userProperty, $place) {

		$sel = $this->enumPlaceDao->findByName($placeName);
		$values->place = $place;

		if ($values->place->value == TRUE) {
			$this->userPlaceDao->insertNewPlace($userProperty->id, $sel->id);
		} else {
			$this->userPlaceDao->deleteSelPlace($userProperty->id, $sel->id);
		}
	}

}
