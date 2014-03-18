<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Utils\Strings,
	Nette\Mail\Message,
	Nette\Utils\Html;


class RegistrationForm extends BaseBootstrapForm
{
	private $id;
    
    public function __construct(IContainer $parent = NULL, $name = NULL)
    {
	parent::__construct($parent, $name);
	
	$presenter = $this->getPresenter();
	
	
	$this->addText('user_name', 'Nick:', 30, 200 )
    		->addRule(Form::FILLED, "Vyplňte svůj nick");
    	$this->addText('email', 'E-mail:', 30, 200 )
    		->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail');
    	$this->addPassword('password', 'Heslo:', 30, 200)
    		->setRequired('Zvolte si heslo')
    		->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 3);
    	$this->addPassword('passwordVerify', 'Heslo pro kontrolu:', 30, 200)
    		->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
    		->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);
    	$this->addSubmit('send', 'Vytvořit')
		->setAttribute('class','btn-main medium');
    	//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
    public function submitted(RegistrationForm $form)
	{
		$values = $form->getValues();
		$mail = $form->getPresenter()->context->createUsers()
			->where('email', $values->email)
			->fetch();
		$values->role = 'user';
		$values['confirmed'] = Strings::random(100);
		
		if($mail){
			$form->addError(Html::el('div')->setText('Tento email už někdo používá.')->setClass('alert alert-danger'));
		} else {
			unset($values->passwordVerify);
			/* odeslání mailu o registraci i s údaji */
			//$this->sendMail($values->mail,$values->password, $values['confirmed']);
			$values->password = \Authenticator::calculateHash($values->password);
			$form->getPresenter()->context->createUsers()
				->insert($values);
			$this->getPresenter()->flashMessage('Registrace proběhla úspěšně, vyčkejte na schválení adminem.');
	        	$form->getPresenter()->redirect('Sign:in');
		}
	
	}
	
	public function sendMail($email,$password, $code)
	{
		$mail = new Message;
		$domain_name = $this->getPresenter()->context->createAuthorizator_table()
				->fetch()
				->domain_name;
		$mail->setFrom('info@nejlevnejsiwebstranky.cz')
			->addTo($email)
			->setSubject('Dokončení registrace')
			->setBody("Dobrý den,\nbyl jste úspěšně zaregistrován. Vaše přihlašovací údaje jsou\ne-mail: " . $email . "\nheslo: " . $password . "Pro dokončení registrace prosím klikněte na tento odkaz:\n" . "http://" . $domain_name . "/sign/in?code=" . $code . "&confirmed=1\n\nDěkujeme za Vaší registraci.\nS pozdravem\ntým nudajefuc.cz")
			->send();
	}

}