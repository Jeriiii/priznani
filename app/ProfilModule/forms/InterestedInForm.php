<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	POS\Model\UserDao,
	POS\Model\UserPropertyDao;

class InterestedInForm extends EditBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 */
	public $userPropertyDao;
	private $id_user;

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->userPropertyDao = $userPropertyDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $this->userDao->find($this->id_user);

		$this->addGroup('Zajímám se o');

		$yesNo = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('threesome', 'Trojku:', $yesNo)
			->setDefaultValue($userInfo->property->threesome);

		$this->addSelect('anal', 'Anální sex:', $yesNo)
			->setDefaultValue($userInfo->property->anal);

		$this->addSelect('group', 'Skupinový:', $yesNo)
			->setDefaultValue($userInfo->property->group);

		$this->addSelect('bdsm', 'BDSM:', $yesNo)
			->setDefaultValue($userInfo->property->bdsm);

		$this->addSelect('swallow', 'Polykání:', $yesNo)
			->setDefaultValue($userInfo->property->swallow);

		$this->addSelect('oral', 'Orální sex:', $yesNo)
			->setDefaultValue($userInfo->property->oral);

		$this->addSelect('piss', 'Piss:', $yesNo)
			->setDefaultValue($userInfo->property->piss);

		$this->addSelect('sex_massage', 'Sex masáž:', $yesNo)
			->setDefaultValue($userInfo->property->sex_massage);

		$this->addSelect('petting', 'Petting:', $yesNo)
			->setDefaultValue($userInfo->property->petting);

		$this->addSelect('fisting', 'Fisting:', $yesNo)
			->setDefaultValue($userInfo->property->fisting);

		$this->addSelect('deepthrought', 'Hluboké kouření:', $yesNo)
			->setDefaultValue($userInfo->property->deepthrought);

		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function editformSubmitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$user = $this->userDao->find($this->id_user);

		$this->userPropertyDao->update($user->propertyID, array('threesome' => $values->threesome, 'anal' => $values->anal, 'group' => $values->group, 'bdsm' => $values->bdsm, 'swallow' => $values->swallow, 'cum' => $values->cum, 'oral' => $values->oral, 'piss' => $values->piss, 'sex_massage' => $values->sex_massage, 'petting' => $values->petting, 'fisting' => $values->fisting, 'deepthrought' => $values->deepthrought,));
		$presenter->flashMessage('Změna doplňujících údajů byla úspěšná');
		$presenter->redirect("this");
	}

}
