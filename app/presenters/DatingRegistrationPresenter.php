<?php

use Nette\Application\UI\Form as Frm;
use POS\Model\UserDao;
use Nette\Utils\Strings;
use Nette\Mail\IMailer;

class DatingRegistrationPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\CoupleDao
	 * @inject
	 */
	public $coupleDao;

	/**
	 * @var \Nette\Mail\IMailer
	 * @inject
	 */
	public $mailer;

	public function startup() {
		parent::startup();

		$this->setLayout("datingLayout");
	}

	public function renderDefault() {
		$this->template->type = $this->getRegSession()->type;
	}

	public function registred() {
		$userSession = $this->getRegSession();

		//SEND EMAIL
		$this->sendMail($userSession);

		if ($userSession->type == 1) {
			$this->flashMessage("Byl jste úspěšně zaregistrován. Prosím potvrďte svůj email.");
		} else if ($userSession->type == 2) {
			$this->flashMessage("Byla jste úspěšně zaregistrována. Prosím potvrďte svůj email.");
		} else {
			$this->flashMessage("Byli jste úspěšně zaregistrováni. Prosím potvrďte svůj email.");
		}

		$userSession->remove();
		$this->getRegSessionForCouple()->remove();

		$this->redirect("OnePage:");
	}

	/**
	 * Zašle email uživateli
	 * @param \Nette\Http\SessionSection $userSession
	 */
	public function sendMail($userSession) {
		$email = $userSession->email;
		$password = $userSession->password;
		$code = $userSession[UserDao::COLUMN_CONFIRMED];
		$regForm = new Frm\RegistrationForm($this->userDao, $this->mailer, $this, "regform");
		$regForm->sendMail($email, $password, $code);
	}

	public function actionThirdRegForm() {
		$type = $this->getRegSession()->type; // typ uživatele
		if ($type == UserDao::PROPERTY_GROUP) {
			$this->redirect("DatingRegistration:register");
		}
	}

	public function renderThirdRegForm() {
		$type = $this->getRegSession()->type; // typ uživatele
		$this->template->firstManLabel = Frm\DatingRegistrationThirdForm::getFirstManName($type);
		$this->template->secondManLabel = Frm\DatingRegistrationThirdForm::getSecondManName($type);
		$this->template->type = $type;

		$this->template->isCouple = Frm\DatingRegistrationThirdForm::isCouple($type);
		$this->template->isFirstMan = Frm\DatingRegistrationThirdForm::isFirstMan($type);
		$this->template->isFirstWoman = Frm\DatingRegistrationThirdForm::isFirstWoman($type);
		$this->template->isSecondMan = Frm\DatingRegistrationThirdForm::isSecondMan($type);
		$this->template->isSecondWoman = Frm\DatingRegistrationThirdForm::isSecondWoman($type);
	}

	public function renderRegistered() {
		$registrationDataUser = $this->getRegSession();
		$this->template->registrationDataUser = $registrationDataUser;
		$this->flashMessage('Registrace byla úspěšná');
	}

	/**
	 * Dokončení registrace uživatel, uložení dat o uživateli do DB
	 * Pokud jde o pár, přesměruje se na zaregistrování partnera
	 */
	public function actionRegister() {
		$this->register();

		$registrationDataUser = $this->getRegSession();
		$type = $registrationDataUser->type; // typ uživatele

		if ($type == UserDao::PROPERTY_COUPLE || $type == UserDao::PROPERTY_COUPLE_MAN || $type == UserDao::PROPERTY_COUPLE_WOMAN) {
			$this->registerCouple();
		}

		/* dokončení registrace */
		$registrationDataUser->firstMemberId = NULL;
		$this->registred();
	}

	/**
	 * Registruje muže, ženu, skupinu či prvního z páru
	 */
	public function register() {
		//session s datama prvniho registrovaneho uzivatele pro vkládání do db
		$registrationDataUser = $this->getRegSession();
		$registrationDataUser[UserDao::COLUMN_CONFIRMED] = Strings::random(29);

		$userProperty = $this->userPropertyDao->registerProperty($registrationDataUser);
		$user = $this->userDao->register($registrationDataUser, $userProperty->id);
		/* aktualizace kódu s jeho id */
		$registrationDataUser[UserDao::COLUMN_CONFIRMED] = $user[UserDao::COLUMN_CONFIRMED];

		$registrationDataUser->firstMemberId = $user->id;
	}

	/**
	 * Uložení dat o páru (druhého z páru) do DB
	 */
	public function registerCouple() {
		$registrationDataCouple = $this->getRegSessionForCouple();
		$registrationDataUser = $this->getRegSession();

		$couple = $this->coupleDao->register($registrationDataCouple);
		$registrationDataCouple->coupleId = $couple->id;

		$this->userDao->setCouple($registrationDataUser->firstMemberId, $couple->id);
	}

	protected function createComponentFirstRegForm($name) {
		return new Frm\DatingRegistrationFirstForm($this->userDao, $this, $name, $this->getRegSession(), $this->getRegSessionForCouple());
	}

	protected function createComponentSecondRegForm($name) {
		return new Frm\DatingRegistrationSecondForm($this->userDao, $this, $name, $this->getRegSession());
	}

	protected function createComponentThirdRegForm($name) {
		return new Frm\DatingRegistrationThirdForm($this->userDao, $this->getRegSession(), $this->getRegSessionForCouple(), $this, $name);
	}

	/**
	 * Vrátí sečnu pro registraci uživatele.
	 * @return \Nette\Http\SessionSection
	 */
	public function getRegSession() {
		return $this->getSession('registrationDataUser');
	}

	/**
	 * Vrátí sečnu pro registraci páru.
	 * @return \Nette\Http\SessionSection
	 */
	public function getRegSessionForCouple() {
		return $this->getSession('registrationDataCouple');
	}

}
