<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
        POS\Model\UserDao,
        POS\Model\UserPropertyDao;

class DatingEditSecondForm extends DatingRegistrationSecondForm {

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
		parent::__construct($userDao, $parent, $name);

                $this->userPropertyDao = $userPropertyDao;
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$userInfo = $this->userDao->find($this->id_user);

		$this->addGroup('Identifikační údaje');
                $email = $userInfo->email;
                $this["email"]->setDisabled();
			
                $this["user_name"]->setDisabled();
                
                
                $this["first_sentence"]->setDefaultValue($userInfo->property->first_sentence);

                $this["about_me"]->setDefaultValue($userInfo->property->about_me);
                
                $this["send"]->caption = "Uložit";
                $this["send"]->setAttribute("class", "btn btn-info");

		return $this;
	}

	public function submitted($form) {
		$values = $form->values;

		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();

		$this->record = $this->userDao->find($this->id_user);
		if (!$this->record) {
			throw new BadRequestException;
		}
		$authenticator = $presenter->context->authenticator;
		$pass = $authenticator->calculateHash($values->password);

		$this->userDao->update($this->id_user, array(/* 'email' => $email, 'user_name' => $user_name, */'password' => $pass));
                $this->userPropertyDao->update($this->record->propertyID, array('first_sentence' => $values->first_sentence, 'about_me' => $values->about_me));
		$presenter->flashMessage('Změna identifikačních údajů byla úspěšná');
		$presenter->redirect("this");
	}

}
