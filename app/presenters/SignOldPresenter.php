<?php

/**
 * Sign in/out presenters.
 *
 * @author     Patrick Kusebauch
 * @package    NudaJeFuc
 */

use Nette\Application\UI,
	Nette\Security as NS,
	Nette\Application\UI\Form as Frm;

class SignOldPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';
	
	public function renderIn($confirmed, $code)
	{
		if($confirmed == 1)
		{
			$user = $this->context->createUsers()
					->where("confirmed", $code)
					->fetch();
			if( empty($user) )
			{
				$this->flashMessage("Potvrzení emailu se nezdařilo, jestli potíže přetrvávají, kontaktujte administrátora stránek.", "error");
			}else{
				$this->context->createUsers()
					->where("confirmed", $code)
					->update(array(
						"role" => "user",
					));
				$this->flashMessage("Potvrzení bylo úspěšné, nyní se můžete přihlásit.", "info");
			}
		}
	}

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
	$form = new UI\Form;
    	$form->addText('mail', 'E-mail:', 30, 200);
    	$form->addPassword('password', 'Heslo:', 30, 200);
    	$form->addCheckbox('persistent', 'Pamatovat si mě na tomto počítači', 30, 200);
    	$form->addSubmit('login', 'PŘIHLÁSIT SE');
    	$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}



	public function signInFormSubmitted($form)
	{
	    try {
	        $user = $this->getUser();
	        $values = $form->getValues();
	        if ($values->persistent) {
	            $user->setExpiration('+30 days', FALSE);
	        }
	        $user->login($values->mail, $values->password);
            //toto se provede při úspěšném zpracování přihlašovacího formuláře
            if (!empty($this->backlink))
	        //$this->getApplication()->restoreRequest($this->backlink);
					
//		$authorizator = new MyAuthorizator;
//		$parametrs = $this->context->createAuthorizator_table()
//				->fetch();
//		//$authorizator->setParametrs(FALSE);
//		$authorizator->setParametrs(
//			$parametrs->galleries == 1 ? TRUE : FALSE,
//			$parametrs->forms == 1 ? TRUE : FALSE,
//			$parametrs->accounts == 1 ? TRUE : FALSE,
//			$parametrs->facebook == 1 ? TRUE : FALSE
//		);
//		$user->setAuthorizator($authorizator);
		
			$this->flashMessage ("Byl jste úspěšně přihlášen");
			if($this->user->isInRole("admin") || $this->user->isInRole("superadmin")) {
				$this->redirect('Admin:Forms:forms');
			} else {
				$this->redirect('Homepage:');
			}
	    } catch (NS\AuthenticationException $e) {
		$form->addError($e->getMessage());
    	}
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('Sign:in');
	}
	
	protected function createComponentRegistrationForm($name) {
		return new Frm\registrationForm($this, $name);
	}
	
	protected function createComponentForgottenPasswordForm($name) {
		return new Frm\forgottenPasswordForm($this, $name);
	}

}
