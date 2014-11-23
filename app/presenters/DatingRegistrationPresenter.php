<?php

use Nette\Application\UI\Form as Frm,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\Database;
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

	public function renderRegistered() {
		$registrationDataUser = $this->getRegSession();
		$this->template->registrationDataUser = $registrationDataUser;
		$this->flashMessage('Registrace byla úspěšná');
	}

	public function actionPreThirdRegForm() {
		$registrationDataUser = $this->getRegSession();
		$registrationDataCouple = $this->getRegSessionForCouple();

		if ($registrationDataUser->type == UserDao::PROPERTY_MAN) {
			$this->setView("thirdRegManForm");
		} else if ($registrationDataUser->type == UserDao::PROPERTY_WOMAN) {
			$this->setView("thirdRegWomanForm");
		} else if ($registrationDataUser->type == UserDao::PROPERTY_GROUP) {
			$this->redirect("DatingRegistration:register");
		} else if ($registrationDataUser->type == UserDao::PROPERTY_COUPLE_WOMAN) {
			$registrationDataCouple->type = UserDao::PROPERTY_WOMAN;
			$this->setView("thirdRegWomanForm");
			$this->template->partnerLabel = "Partnerka";
		} else if ($registrationDataUser->type == UserDao::PROPERTY_COUPLE) {
			$registrationDataCouple->type = UserDao::PROPERTY_MAN;
			$this->setView("thirdRegWomanForm");
			$this->template->partnerLabel = "Partnerka";
		} else if ($registrationDataUser->type == UserDao::PROPERTY_COUPLE_MAN) {
			$registrationDataCouple->type = UserDao::PROPERTY_MAN;
			$this->template->partnerLabel = "Partner";
			$this->setView("thirdRegManForm");
		}
	}

	/**
	 * Dokončení registrace uživatel, uložení dat o uživateli do DB
	 * Pokud jde o pár, přesměruje se na zaregistrování partnera
	 */
	public function actionRegister() {
		//session s datama prvniho registrovaneho uzivatele pro vkládání do db
		$registrationDataUser = $this->getRegSession();
		$registrationDataUser[UserDao::COLUMN_CONFIRMED] = Strings::random(29);

		$userProperty = $this->userPropertyDao->registerProperty($registrationDataUser);
		$user = $this->userDao->register($registrationDataUser, $userProperty->id);
		/* aktualizace kódu s jeho id */
		$registrationDataUser[UserDao::COLUMN_CONFIRMED] = $user[UserDao::COLUMN_CONFIRMED];

		$registrationDataUser->firstMemberId = $user->id;

		if ($registrationDataUser->type == 5) {
			$this->redirect("DatingRegistration:fourthRegWomanForm");
		} else if ($registrationDataUser->type == 4 || $registrationDataUser->type == 3) {
			$this->redirect("DatingRegistration:fourthRegManForm");
		} else {
			/* dokončení registrace */
			$registrationDataUser->firstMemberId = NULL;
			$this->registred();
		}
	}

	/**
	 * Uložení dat o páru do DB
	 */
	public function actionRegisterCouple() {
		$registrationDataCouple = $this->getRegSessionForCouple();
		$registrationDataUser = $this->getRegSession();

		$couple = $this->coupleDao->register($registrationDataCouple);
		$registrationDataCouple->coupleId = $couple->id;

		$this->userDao->setCouple($registrationDataUser->firstMemberId, $couple->id);

		$this->registred();
	}

	protected function createComponentFirstRegForm($name) {
		return new Frm\DatingRegistrationFirstForm($this->userDao, $this, $name, $this->getRegSession(), $this->getRegSessionForCouple());
	}

	protected function createComponentSecondRegForm($name) {
		return new Frm\DatingRegistrationSecondForm($this->userDao, $this, $name, $this->getRegSession());
	}

	protected function createComponentThirdRegManForm($name) {
		return new Frm\DatingRegistrationManThirdForm($this->userDao, $this, $name, $this->getRegSession());
	}

	protected function createComponentThirdRegWomanForm($name) {
		return new Frm\DatingRegistrationWomanThirdForm($this->userDao, $this, $name, $this->getRegSession());
	}

	protected function createComponentFourthRegWomanForm($name) {
		return new Frm\DatingRegistrationWomanFourthForm($this->userDao, $this, $name, $this->getRegSessionForCouple());
	}

	protected function createComponentFourthRegManForm($name) {
		return new Frm\DatingRegistrationManFourthForm($this->userDao, $this, $name, $this->getRegSessionForCouple());
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
