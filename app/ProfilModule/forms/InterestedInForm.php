<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;

class InterestedInForm extends EditBaseForm {

	private $userModel;
	private $id_user;
	private $record;

	public function __construct(IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $presenter->context->userModel->findUser(array('id' => $this->id_user));

		$this->addGroup('Zajímám se o');
		$threesome = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('threesome', 'Trojku:', $threesome)
			->setDefaultValue($userInfo->threesome);

		$anal = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('anal', 'Anální sex:', $anal)
			->setDefaultValue($userInfo->anal);

		$group = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('group', 'Skupinový:', $group)
			->setDefaultValue($userInfo->group);

		$bdsm = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('bdsm', 'BDSM:', $bdsm)
			->setDefaultValue($userInfo->bdsm);

		$swallow = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('swallow', 'Polykání:', $swallow)
			->setDefaultValue($userInfo->swallow);


		$cum = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('cum', 'Sperma:', $cum)
			->setDefaultValue($userInfo->cum);

		$oral = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('oral', 'Orální sex:', $oral)
			->setDefaultValue($userInfo->oral);

		$piss = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('piss', 'Piss:', $piss)
			->setDefaultValue($userInfo->piss);

		$sex_massage = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('sex_massage', 'Sex masáž:', $sex_massage)
			->setDefaultValue($userInfo->sex_massage);

		$petting = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('petting', 'Osahávání:', $petting)
			->setDefaultValue($userInfo->petting);

		$fisting = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('fisting', 'Fisting:', $fisting)
			->setDefaultValue($userInfo->fisting);

		$deepthrought = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('deepthrought', 'Hluboké kouření:', $deepthrought)
			->setDefaultValue($userInfo->deepthrought);

		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function editformSubmitted($form) {
		$values = $form->values;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$this->record = $presenter->context->userModel->findUser(array('id' => $this->id_user));
		if (!$this->record) {
			throw new BadRequestException;
		}
		$presenter->context->userModel->updateUser($this->record, array('threesome' => $values->threesome, 'anal' => $values->anal, 'group' => $values->group, 'bdsm' => $values->bdsm, 'swallow' => $values->swallow, 'cum' => $values->cum, 'oral' => $values->oral, 'piss' => $values->piss, 'sex_massage' => $values->sex_massage, 'petting' => $values->petting, 'fisting' => $values->fisting, 'deepthrought' => $values->deepthrought,));
		$presenter->flashMessage('Změna doplňujících údajů byla úspěšná');
		$presenter->redirect("EditProfil:default");
	}

}
