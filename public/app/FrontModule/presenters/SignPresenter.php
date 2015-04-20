<?php

/**
 * Sign in/out presenters.
 *
 * @author     Patrick Kusebauch
 * @package    NudaJeFuc
 */
use Nette\Security as NS;
use Nette\DateTime;
use POS\Model\UserDao;
use Nette\Application\UI\Form as Frm;

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

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\UserChangePasswordDao @inject */
	public $userChangePasswordDao;

	/** @var \Nette\Mail\IMailer @inject */
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
				if (empty($user->last_signed_in)) { //už se někdy přihlásil?
					$this->userDao->setUserRoleByConfirm($code);
					$identity = new NS\Identity($user->id, $user->role, $user->toArray());
					$this->getUser()->login($identity);
					$this->getUser()->setExpiration('30 minutes', TRUE);

					/* zaznamenání, že se uživatel poprvé přihlásil */
					$user->update(array(
						UserDao::COLUMN_LAST_SIGNED_DAY => new DateTime()
					));

					$this->flashMessage("Potvrzení bylo úspěšné, systém vás automaticky přihlásil.", "info");
					$this->redirect("OnePage:");
				} else {
					$this->flashMessage("Potvrzení bylo úspěšné, můžete se přihlásil.", "info");
					$this->redirect("Sign:in");
				}
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
		$this->getUser()->logout();
		$this->getSession()->destroy();
		$this->flashMessage("Byl jste úspěšně odhlášen");
		$this->redirect('Sign:in');
	}

	public function actionRegistration() {
		$this->redirect(":DatingRegistration:");
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
