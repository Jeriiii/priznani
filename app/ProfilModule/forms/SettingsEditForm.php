<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use Nette\Database\Table\ActiveRow;
use POS\Model\UserPropertyDao;
use POS\Model\DistrictDao;
use POS\Model\RegionDao;
use POS\Model\CityDao;

/**
 * Změna nastavení profilu
 *
 * @author Jan Kotalík
 */
class SettingsEditForm extends BaseForm {

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;

	/**
	 * @var ActiveRow
	 */
	private $property;

	public function __construct(UserPropertyDao $userPropertyDao, $property, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->userPropertyDao = $userPropertyDao;
		$this->property = $property;

		$this->addGroup("Chat");

		$this->addCheckbox('sound_effect', 'Přehrát zvuk při přijetí zprávy');

		if (isset($property->sound_effect)) {
			if ($property->sound_effect == 0) {
				$this->setDefaults(array('sound_effect' => false));
			} else {
				$this->setDefaults(array('sound_effect' => true));
			}
		}

		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn-main medium button");
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(SettingsEditForm $form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		if ($values['sound_effect']) {
			$soundEffect = 1;
		} else {
			$soundEffect = 0;
		};

		$this->userPropertyDao->update($this->property->id, array(
			UserPropertyDao::COLUMN_SOUND_EFFECT => $soundEffect
		));

		$presenter->calculateLoggedUser();
		$presenter->flashMessage('Nastavení změněno.');
		$presenter->redirect('this');
	}

}
