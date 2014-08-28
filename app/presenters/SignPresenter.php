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
use POS\UserPreferences\StreamUserPreferences;

class SignPresenter extends BasePresenter {

	/**
	 * 	Jméno session, kam se ukládají podružné informace o uživateli (nesouvisející s identitou)
	 */
	const USER_INFO_SESSION_NAME = 'userinfo';

	/**
	 * @var boolean Pokud je TRUE, existuje v session odkaz kam chceme po
	 * přihlášení přesměrovat.
	 * @persistent
	 */
	public $backlink = FALSE;

	/**
	 * @var array Pole proměnných ve zpětném odkazu.
	 * @persistent
	 */
	private $backquery;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserChangePasswordDao
	 * @inject
	 */
	public $userChangePasswordDao;

	/**
	 * @var \Nette\Mail\IMailer
	 * @inject
	 */
	public $mailer;

	/* uživatel pro práci se změnou hesla */
	public $userForPassChange;

	public function startup() {
		parent::startup();
		if ($this->getUser()->isLoggedIn() && $this->action != "out") {
			$this->redirect("OnePage:");
		}
	}

	public function actionIn($backlink) {
		$this->backlink = $backlink;
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

	public function actionChangePassword($ticket) {
		$dbData = $this->userChangePasswordDao->findTicket($ticket);
		$date = new Nette\DateTime();
		$date->modify('-7 days');

		if (!$dbData) {
			$this->flashMessage("Žádost o nové heslo nebyla nalezena, odešlete prosím novou.", "info");
			$this->redirect("Sign:forgottenPass");
		}
		if ($dbData->create < $date) {
			$this->flashMessage("Žádost o nové heslo je příliš stará, odešlete prosím novou.", "info");
			$this->redirect("Sign:forgottenPass", array("email" => $dbData->user->email));
		}

		$this->userForPassChange = $dbData->user;
	}

	public function actionOut() {
		$this->getSession('allow')->remove();
		$this->getUser()->logout();
		$this->getSession(self::USER_INFO_SESSION_NAME)->remove();
		$this->getSession(StreamUserPreferences::NAME_SESSION_BEST_STREAM_ITEMS)->remove();
		$this->flashMessage("Byl jste úspěšně odhlášen");
		$this->redirect('Sign:in');
	}

	/**
	 * Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm($name) {
		return new Frm\signInForm($this->backlink, $this, $name);
	}

	protected function createComponentRegistrationForm($name) {
		return new Frm\registrationForm($this->userDao, $this->mailer, $this, $name);
	}

	protected function createComponentForgottenPasswordForm($name) {
		return new Frm\forgottenPasswordForm($this->userChangePasswordDao, $this->userDao, $this->mailer, $this, $name);
	}

	protected function createComponentChangePasswordForm($name) {
		return new Frm\ChangePasswordForm($this->userDao, $this->userChangePasswordDao, $this->userForPassChange, $this->mailer, $this, $name);
	}

}
