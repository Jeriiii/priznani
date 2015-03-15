<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form,
	Nette\Security as NS,
    Nette\ComponentModel\IContainer;


class PasswordForm extends Form
{
    public function __construct(IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
        //graphics
        $renderer = $this->getRenderer();
		$renderer->wrappers['controls']['container'] = 'div';
		$renderer->wrappers['pair']['container'] = 'div';
		$renderer->wrappers['label']['container'] = NULL;
		$renderer->wrappers['control']['container'] = 'div';
    	//form
        $this->addPassword('oldPassword', 'Staré heslo:', 30)
            ->addRule(Form::FILLED, 'Je nutné zadat staré heslo.');
        $this->addPassword('newPassword', 'Nové heslo:', 30)
            ->addRule(Form::MIN_LENGTH, 'Nové heslo musí mít alespoň %d znaků.', 6);
        $this->addPassword('confirmPassword', 'Potvrzení hesla:', 30)
            ->addRule(Form::FILLED, 'Nové heslo je nutné zadat ještě jednou pro potvrzení.')
            ->addRule(Form::EQUAL, 'Zadná hesla se musejí shodovat.', $this['newPassword']);
        $this->addSubmit('send', 'Změnit heslo');
    	$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
    	$this->onSuccess[] = callback($this, 'submitted');
    	return $this;
    }
    
    public function submitted(PasswordForm $form)
    {
        $values = $form->getValues();
        $presenter = $this->getPresenter();
        $user = $presenter->getUser();
        try {
            $presenter->context->authenticator->authenticate(array($user->getIdentity()->mail, $values->oldPassword));
            $presenter->context->authenticator->setPassword($user->getId(), $values->newPassword);
            $presenter->flashMessage('Heslo bylo změněno.', 'info');
            $presenter->redirect('this');
        } catch (NS\AuthenticationException $e) {
            $this->addError('Zadané heslo není správné.');
        }
    }
	
}
