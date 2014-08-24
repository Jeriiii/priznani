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
	private $record;

	public function __construct(UserPropertyDao $userPropertyDao, UserDao $userDao, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

                $this->userDao = $userDao;
                $this->userPropertyDao = $userPropertyDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $this->userDao->find($this->id_user);

		$this->addGroup('Zajímám se o');
		$threesome = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('threesome', 'Trojku:', $threesome)
			->setDefaultValue($userInfo->property->threesome);

		$anal = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('anal', 'Anální sex:', $anal)
			->setDefaultValue($userInfo->property->anal);

		$group = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('group', 'Skupinový:', $group)
			->setDefaultValue($userInfo->property->group);

		$bdsm = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('bdsm', 'BDSM:', $bdsm)
			->setDefaultValue($userInfo->property->bdsm);

		$swallow = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('swallow', 'Polykání:', $swallow)
			->setDefaultValue($userInfo->property->swallow);


		$cum = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('cum', 'Sperma:', $cum)
			->setDefaultValue($userInfo->property->cum);

		$oral = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('oral', 'Orální sex:', $oral)
			->setDefaultValue($userInfo->property->oral);

		$piss = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('piss', 'Piss:', $piss)
			->setDefaultValue($userInfo->property->piss);

		$sex_massage = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('sex_massage', 'Sex masáž:', $sex_massage)
			->setDefaultValue($userInfo->property->sex_massage);

		$petting = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('petting', 'Osahávání:', $petting)
			->setDefaultValue($userInfo->property->petting);

		$fisting = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('fisting', 'Fisting:', $fisting)
			->setDefaultValue($userInfo->property->fisting);

		$deepthrought = array(
			'0' => 'ne',
			'1' => 'ano',
		);
		$this->addSelect('deepthrought', 'Hluboké kouření:', $deepthrought)
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

		$this->record = $this->userDao->find($this->id_user);
		if (!$this->record) {
			throw new BadRequestException;
		}
		$this->userPropertyDao->update($this->record->propertyID, array('threesome' => $values->threesome, 'anal' => $values->anal, 'group' => $values->group, 'bdsm' => $values->bdsm, 'swallow' => $values->swallow, 'cum' => $values->cum, 'oral' => $values->oral, 'piss' => $values->piss, 'sex_massage' => $values->sex_massage, 'petting' => $values->petting, 'fisting' => $values->fisting, 'deepthrought' => $values->deepthrought,));
		$presenter->flashMessage('Změna doplňujících údajů byla úspěšná');
		$presenter->redirect("this");
	}

}
