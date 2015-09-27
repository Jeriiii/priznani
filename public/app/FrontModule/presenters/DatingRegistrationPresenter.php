<?php

use Nette\Application\UI\Form as Frm;
use POS\Model\UserDao;
use Nette\Utils\Strings;
use Nette\Mail\IMailer;
use Nette\DateTime;
use POSComponent\Search\AnonymousSearch;

class DatingRegistrationPresenter extends BasePresenter {

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \POS\Model\UserPropertyDao @inject */
	public $userPropertyDao;

	/** @var \POS\Model\CoupleDao @inject */
	public $coupleDao;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var \POS\Model\UserCategoryDao @inject */
	public $userCategoryDao;

	/** @var \POS\Model\PaymentDao @inject */
	public $paymentDao;

	public function startup() {
		parent::startup();

		$this->setLayout("datingLayout");
	}

	public function renderDefault($justRegistred = false) {
		$this->template->type = $this->getRegSession()->type;
		$this->template->features = array(
			array('image' => 'features1', 'name' => '0 KČ ZA SEZNÁMENÍ', 'text' => 'Unavuje tě všude za vše platit? U nás se seznamujeme <strong>zdarma</strong>.'),
			array('image' => 'features2', 'name' => 'PŘIZNÁNÍ O SEXU', 'text' => 'Přes 10 000 pikantních postelových přiznání k pobavení i poučení na Datenode.cz'),
			array('image' => 'features3', 'name' => 'BLOKACE', 'text' => 'Chceme, aby se u nás každý cítil dobře. V případě, že Tě někdo obtěžuje, jednoduše ho zablokuj.'),
			array('image' => 'features4', 'name' => 'TLAČÍTKO PANIKA', 'text' => 'Blíží se šéf? Žárlivý partner? U nás Tě nenachytají! Jedním tlačítkem Tě okamžitě odhlásíme a přehodíme na neškodnou stránku.'),
			array('image' => 'features5', 'name' => 'SOUKROMÉ GALERIE', 'text' => 'Vyber si, komu chceš ukázat své lechtivější fotky!'),
			array('image' => 'features6', 'name' => 'JAK MOC JSI SEXY?', 'text' => 'Jsi zajíček, kanec, Casanova, dračice nebo bohyně? Zjistíš podle hodnocení jiných uživatelů!'),
			array('image' => 'features7', 'name' => 'VĚŘÍŠ NA HOROSKOP?', 'text' => 'Najděte se dle oblíbeného znamení zvěrokruhu, jen na Datenode.cz!'),
			array('image' => 'features8', 'name' => 'VYSTUPUJTE ZA PÁR', 'text' => 'Jste dva a nechcete mít dva profily? U nás máte možnost PÁR, pro lepší seznamování!'),
		);
		$this->template->justRegistred = $justRegistred;
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

		$this->redirect("DatingRegistration:", array('justRegistred' => TRUE));
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

	public function actionFourthRegForm() {
		$type = $this->getRegSession()->type; // typ uživatele
		if ($type == UserDao::PROPERTY_GROUP) {
			$this->redirect("DatingRegistration:register");
		}
	}

	public function renderFourthRegForm() {
		$type = $this->getRegSession()->type; // typ uživatele
		$this->template->firstManLabel = Frm\DatingRegistrationFourthForm::getFirstManName($type);
		$this->template->secondManLabel = Frm\DatingRegistrationFourthForm::getSecondManName($type);
		$this->template->type = $type;

		$this->template->isCouple = Frm\DatingRegistrationFourthForm::isCouple($type);
		$this->template->isFirstMan = Frm\DatingRegistrationFourthForm::isFirstMan($type);
		$this->template->isFirstWoman = Frm\DatingRegistrationFourthForm::isFirstWoman($type);
		$this->template->isSecondMan = Frm\DatingRegistrationFourthForm::isSecondMan($type);
		$this->template->isSecondWoman = Frm\DatingRegistrationFourthForm::isSecondWoman($type);
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
		$registrationDataUser = $this->getRegSession();

		if ($registrationDataUser->age === NULL) { //pravděpodobně jde o robota který neprošel prvním formulářem
			$this->flashMessage("Registrace nemohla být dokončena, protože jste nevyplnili svůj věk.");
			$this->redirect("DatingRegistration:");
		}

		$this->register();

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

		$userProperty = $this->userPropertyDao->registerProperty($registrationDataUser, $this->userCategoryDao);
		$user = $this->userDao->register($registrationDataUser, $userProperty->id);
		/* aktualizace kódu s jeho id */
		$registrationDataUser[UserDao::COLUMN_CONFIRMED] = $user[UserDao::COLUMN_CONFIRMED];

		/* 14 dní premium účtu */
		$now = new DateTime();
		$addTime = new DateTime(); //přidaný čas, kdy bude premium
		if ($registrationDataUser->oldUser) {
			$addTime->modify('+21 days');
		} else {
			$addTime->modify('+14 days');
		}
		$this->paymentDao->insertPremium($now, $addTime, $user->id);

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
		return new Frm\DatingRegistrationSecondForm($this->userDao, $this, $name, $this->getRegSession(), $this->getRegSessionForCouple());
	}

	protected function createComponentThirdRegForm($name) {
		return new Frm\DatingRegistrationThirdForm($this->userDao, $this, $name, $this->getRegSession());
	}

	protected function createComponentFourthRegForm($name) {
		return new Frm\DatingRegistrationFourthForm($this->userDao, $this->getRegSession(), $this->getRegSessionForCouple(), $this, $name);
	}

	protected function createComponentMatchingUsers($name) {
		return new AnonymousSearch($this->getRegSession(), $this->userDao, $this->userCategoryDao, $this, $name);
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
