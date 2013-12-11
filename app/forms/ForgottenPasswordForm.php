<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Mail\Message,
	Nette\Utils\Strings;


class ForgottenPasswordForm extends UI\Form
{
    public function __construct(IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
        //graphics
//        $renderer = $this->getRenderer();
//		$renderer->wrappers['controls']['container'] = 'div';
//		$renderer->wrappers['pair']['container'] = 'div';
//		$renderer->wrappers['label']['container'] = NULL;
//		$renderer->wrappers['control']['container'] = 'div';
    	//form
    	$this->addText('mail', 'Email:', 30, 50);
    	$this->addSubmit('send', 'Vygenerovat nové heslo');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
	public function submitted(ForgottenPasswordForm $form)
	{
		$values = $form->getValues();
		$presenter = $this->getPresenter();
		
		$user = $presenter->context->createUsers()
				->where("mail", $values->mail)
				->fetch();
		if( empty($user) )
		{
			$form->addError('Mail nebyl nalezen.');
		}else{
			$password = Strings::random(15);
			$presenter->context->createUsers()
					->where("mail", $values->mail)
					->update(array(
						"password" => \Authenticator::calculateHash($password),
					));
			$this->sendMail($user->mail, $password);
			$presenter->flashMessage("Heslo bylo odesláno mailem");
		}
	}
	
	public function sendMail($email,$password){
	    $mail = new Message;
	    $mail->setFrom('info@nejlevnejsiwebstranky.cz')
		->addTo($email)
		->setSubject('Zapomenuté heslo')
		->setBody("Dobrý den,\nVaše přihlašovací údaje jsou:\ne-mail: ".$email."\nheslo: ".$password."\n\nS pozdravem\ntým nejlevnejsiwebstranky.cz")
		->send();
	}
}
