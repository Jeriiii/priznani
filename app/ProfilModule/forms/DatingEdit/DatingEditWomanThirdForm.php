<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
        POS\Model\UserDao,
        POS\Model\UserPropertyDao;

class DatingEditWomanThirdForm extends DatingRegistrationBaseWomanForm {

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
		$this->addGroup('Osobní údaje');
		parent::__construct($userDao, $parent, $name);

		$presenter = $this->getPresenter();
                $this->userDao = $userDao;
                $this->userPropertyDao = $userPropertyDao;
		$this->id_user = $presenter->getUser()->getId();
		$userInfo = $this->userDao->find($this->id_user);

		$this->setDefaults(array(
			'marital_state' => $userInfo->property->marital_state,
			'orientation' => $userInfo->property->orientation,
			'tallness' => $userInfo->property->tallness,
			'shape' => $userInfo->property->shape,
			'smoke' => $userInfo->property->smoke,
			'drink' => $userInfo->property->drink,
			'graduation' => $userInfo->property->graduation,
			'bra_size' => $userInfo->property->bra_size,
			'hair_colour' => $userInfo->property->hair_colour,
		));


		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Uložit')
			->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		parent::submitted($form);
		$values = $form->values;

		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$this->record = $this->userDao->find($this->id_user);
		if (!$this->record) {
			throw new BadRequestException;
		}

		$this->userPropertyDao->update($this->record->propertyID, array('marital_state' => $values->marital_state, 'orientation' => $values->orientation, 'tallness' => $values->tallness, 'shape' => $values->shape, 'smoke' => $values->smoke, 'drink' => $values->drink, 'graduation' => $values->graduation, 'bra_size' => $values->bra_size, 'hair_colour' => $values->hair_colour));
		$presenter->flashMessage('Změna osobních údajů byla úspěšná');
		$presenter->redirect("this");
	}

}