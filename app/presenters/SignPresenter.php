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

class SignPresenter extends BasePresenter {

	/** @persistent */
	public $backlink = '';

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \Nette\Mail\IMailer
	 * @inject
	 */
	public $mailer;

	public function startup() {
		parent::startup();
		if ($this->getUser()->isLoggedIn() && $this->action != "out") {
			$this->redirect("OnePage:");
		}
	}

	public function renderIn($confirmed, $code) {
		if ($confirmed == 1) {
			$user = $this->userDao->findByConfirm($code);
			if (empty($user)) {
				$this->flashMessage("Potvrzení emailu se nezdařilo, jestli potíže přetrvávají, kontaktujte administrátora stránek.", "error");
			} else {
				$this->userDao->setUserRoleByConfirm($code);
				$this->flashMessage("Potvrzení bylo úspěšné, nyní se můžete přihlásit.", "info");
			}
		}
	}

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm($name) {
		return new Frm\signInForm($this, $name);
	}

	public function actionOut() {
		$this->getSession('allow')->remove();
		$this->getUser()->logout();
		$this->flashMessage("Byl jste úspěšně odhlášen");
		$this->redirect('Sign:in');
	}

	protected function createComponentRegistrationForm($name) {
		return new Frm\registrationForm($this->userDao, $this, $name);
	}

	protected function createComponentForgottenPasswordForm($name) {
		return new Frm\forgottenPasswordForm($this->userDao, $this->mailer, $this, $name);
	}

}
