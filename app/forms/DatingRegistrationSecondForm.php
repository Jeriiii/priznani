<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer;


class DatingRegistrationSecondForm extends BaseForm
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->addText('email', 'Email')
			->addRule(Form::FILLED, 'Email není vyplněn.')
			->addRule(Form::EMAIL, 'Vyplněný email není platného formátu.')
			->addRule(Form::MAX_LENGTH, 'Email je příliž dlouhý.', 50);
		$this->addText('user_name', 'Uživatelské jméno')
			->addRule(Form::FILLED, 'Uživatelské jméno není vyplněno')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Uživatelské jméno\" je 100 znaků.', 20);
		$this->addPassword('password', 'Heslo')
			->addRule(Form::FILLED, 'Heslo není vyplněno.')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 4)
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Heslo\" je 100 znaků.', 100);
		$this->addPassword('passwordVerify', 'Heslo pro kontrolu:')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
		$this->addText('first_sentence', 'Úvodní věta (max 100 znaků)')
			->addRule(Form::FILLED, 'Úvodní věta není vyplněna.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"Úvodní věta\" je 100 znaků.', 100);
		$this->addTextArea('about_me', 'O mě (max 300 znaků)', 40, 10)
			->addRule(Form::FILLED, 'O mě není vyplněno.')
			->addRule(Form::MAX_LENGTH, 'Maximální délka pole \"O mě\" je 300 znaků.', 300);

		$this->onSuccess[] = callback($this, 'submitted');
		$this->addSubmit('send', 'Do třetí části registrace')
				->setAttribute("class", "btn btn-success");
		
		return $this; 
	}
	public function submitted($form)
	{
		$values = $form->values;
		$presenter = $this->getPresenter();
		$container = $presenter->context;
		
		$user_name = $form->getPresenter()->context->createUsers()->where('user_name', $values->user_name)->fetch();
		if($user_name){
			$form->addError('Toto jméno je již obsazeno.');
		}else{
			$email = $form->getPresenter()->context->createUsers()->where('email', $values->email)->fetch();
			if($email){
				$form->addError('Tento mail již někdo používá.');
			} else {
				$authenticator = $presenter->context->authenticator;
				$pass = $authenticator->calculateHash($values->password);
				$presenter->redirect('Datingregistration:PreThirdRegForm', $values->email, $values->user_name, $pass, $values->first_sentence, $values->about_me);
			}
		}
	}
}