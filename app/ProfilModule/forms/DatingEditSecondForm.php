<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class DatingEditSecondForm extends EditBaseForm
{
	private $userModel;
	private $id_user;
	private $record;
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		
		$presenter = $this->getPresenter();
		$this->userModel = $this->getPresenter()->context->userModel;
		$this->id_user = $presenter->getUser()->getId();
                
		$userInfo = $presenter->getContext()->userModel->findUser(array('id' => $this->id_user));

		$this->addGroup('Identifikační údaje');
		$this->addText('email', 'Email')
			->setDefaultValue($userInfo->email)
			->addRule(Form::FILLED, 'Email není vyplněn.')
			->addRule(Form::EMAIL, 'Vyplněný email není platného formátu.')
			->addRule(Form::MAX_LENGTH, 'Email je příliž dlouhý.', 50)
			->setDisabled();
			
		$this->addText('user_name', 'Uživatelské jméno')
			->setDefaultValue($userInfo->user_name)
			->addRule(Form::FILLED, 'Uživatelské jméno není vyplněno')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Uživatelské jméno\" je 100 znaků.', 20)
			->setDisabled();
			
		$this->addPassword('password', 'Heslo')
			->addRule(Form::FILLED, 'Heslo není vyplněno.')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 4)
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Heslo\" je 100 znaků.', 100);
			
		$this->addPassword('passwordVerify', 'Heslo pro kontrolu:')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
			
		$this->addText('first_sentence', 'Úvodní věta')
			->setDefaultValue($userInfo->first_sentence)
			->addRule(Form::FILLED, 'Úvodní věta není vyplněna.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Úvodní věta\" je 200 znaků.', 200);
			
		$this->addText('about_me', 'O mě')
			->setDefaultValue($userInfo->about_me)
			->addRule(Form::FILLED, 'O mě není vyplněno.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"O mě\" je 300 znaků.', 300);
			

		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('send', 'Uložit')
				->setAttribute("class", "btn btn-info");
		
		return $this; 
	}
	public function editformSubmitted($form)
	{
		$values = $form->values;
		
		$presenter = $this->getPresenter();
		$this->id_user = $presenter->getUser()->getId();
		
		$this->record = $presenter->context->userModel->findUser(array('id' => $this->id_user) );
		if (!$this->record) {
            throw new BadRequestException;
        } 
		$authenticator = $presenter->context->authenticator;
		$pass = $authenticator->calculateHash($values->password);
				
		$presenter->context->userModel->updateUser($this->record, array(/*'email' => $email, 'user_name' => $user_name, */'password' => $pass, 'first_sentence' => $values->first_sentence, 'about_me' => $values->about_me) );		
		$presenter->flashMessage('Změna identifikačních údajů byla úspěšná');
		$presenter->redirect("Editprofil:default");		
	} 
}