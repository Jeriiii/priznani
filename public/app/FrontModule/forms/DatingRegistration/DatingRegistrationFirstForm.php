<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use POS\Model\UserDao;
use Nette\Http\SessionSection;
use Nette\DateTime;

/**
 * První formulář registrace
 */
class DatingRegistrationFirstForm extends DatingRegistrationBaseForm {

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/** @var \Nette\Http\SessionSection */
	private $regSession;

	/** @var \Nette\Http\SessionSection */
	private $regCoupleSession;

	public function __construct(UserDao $userDao, IContainer $parent = NULL, $name = NULL, $regSession = NULL, $regCoupleSession = NULL, $isRegistration = TRUE) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->regSession = $regSession;
		$this->regCoupleSession = $regCoupleSession;

		$this->addGroup('Základní údaje:');

		$secondAge = !empty($regCoupleSession) ? $regCoupleSession->age : NULL;
		$this->addAge($regSession->age, $secondAge, $regSession->type, $isRegistration);

		$this->addSelect('type', 'Jsem:', $this->userDao->getUserPropertyOption());

		$this->onSuccess[] = callback($this, 'submitted');
		$this->onValidate[] = callback($this, 'validateAge');
		$this->addSubmit('send', 'Do druhé části registrace')
			->setAttribute("class", "btn btn-main");

		return $this;
	}

	public function submitted($form) {
		$values = $form->getValues();
		$presenter = $this->getPresenter();

		$this->regSession->role = 'unconfirmed_user';
		$this->regSession->age = $this->getAge($values);
		$this->regCoupleSession->age = $this->getSecondAge($values);
		$this->regSession->vigor = $this->getVigor($this->regSession->age);
		$this->regSession->type = $values->type;

		$presenter->redirect('Datingregistration:SecondRegForm');
	}

}
