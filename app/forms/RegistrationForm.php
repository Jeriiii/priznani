<?php

namespace Nette\Application\UI\Form;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Strings;
use Nette\Mail\Message;
use Nette\Utils\Html;
use POS\Model\UserDao;
use Authorizator;
use NetteExt\Path\GalleryPathCreator;
use NetteExt\File;
use Nette\Mail\IMailer;

class RegistrationForm extends BaseBootstrapForm {

	private $id;

	/**
	 * @var \POS\Model\UserDao
	 */
	public $userDao;

	/** @var \Nette\Mail\IMailer */
	private $mailer;

	public function __construct(UserDao $userDao, IMailer $mailer, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->userDao = $userDao;
		$this->mailer = $mailer;

		$emailNameNote = Html::el('small')->setText(" (pro zapomenuté heslo, soutěže a pod.)");
		$emailName = Html::el()->setText('E-mail:')->add($emailNameNote);

		$this->addText('user_name', 'Nick:', 30, 200)
			->addRule(Form::FILLED, "Vyplňte svůj nick");
		$this->addText('email', $emailName, 30, 200)
			->addRule(Form::EMAIL, 'Zadali jste neplatný e-mail');
		$this->addPassword('password', 'Heslo:', 30, 200)
			->setRequired('Zvolte si heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 3);
		$this->addPassword('passwordVerify', 'Heslo pro kontrolu:', 30, 200)
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $this['password']);

		$this->addCheckbox("adult", "Už mi bylo 18 let")
			->addRule(Form::FILLED, "Musí Vám být alespoň 18 let.");
		$this->addCheckbox("agreement", Html::el("span")
				->setText("Souhlasím s ")
				->add(
					Html::el('a')
					->href("http://priznaniosexu.cz/smlouvy/registrace.pdf")
					->setHtml('<u>podmínkami</u>'))
			)
			->addRule(Form::FILLED, "Musíte souhlasit s podmínkami.");

		$this->addSubmit('send', 'Registrovat')
			->setAttribute('class', 'btn-main medium');
		//$this->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(RegistrationForm $form) {
		$values = $form->getValues();

		unset($values["adult"]);
		unset($values["agreement"]);

		$mail = $this->userDao->findByEmail($values->email);
		$nick = $this->userDao->findByUserName($values->user_name);

		$values->role = Authorizator::ROLE_USER;
		$values[UserDao::COLUMN_CONFIRMED] = Strings::random(100);

		if ($mail) {
			$form->addError('Tento email už někdo používá.');
		} elseif ($nick) {
			$form->addError('Tento nick už někdo používá.');
		} else {
			unset($values->passwordVerify);
			/* odeslání mailu o registraci i s údaji */
			$this->sendMail($values->email, $values->password, $values[UserDao::COLUMN_CONFIRMED]);
			$values->password = \Authenticator::calculateHash($values->password);
			$user = $this->userDao->insert($values);

			//vytvoření nové složky pro vlastní galerii
			$galleryPath = GalleryPathCreator::getBasePathForAllUserGalleries($user->id);
			File::createDir($galleryPath);

			$this->getPresenter()->flashMessage('Pro dokončení registrace prosím klikněte na odkaz zaslaný na Váš email.');
			$form->getPresenter()->redirect('Sign:in');
		}
	}

	public function sendMail($email, $password, $code) {
		$link = $this->getPresenter()->link("//:Sign:in", array("code" => $code, "confirmed" => 1));

		$mail = new Message;
		$mail->setFrom('neodepisuj@priznaniosexu.cz')
			->addTo($email)
			->setSubject('Dokončení registrace')
			->setBody("Dobrý den,\nbyl jste úspěšně zaregistrován. Vaše přihlašovací údaje jsou\ne-mail: " . $email . "\nheslo: " . $password . "\nPro dokončení registrace prosím klikněte na tento odkaz:\n" . $link . "\n\nDěkujeme za Vaší registraci.\nS pozdravem\ntým priznaniosexu.cz");

		$this->mailer->send($mail);
	}

}
