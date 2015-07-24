<?php

/**
 * Stream na úvodní stránce, zobrazuje všechny příspěvky co by chtěl uživatel vidět.
 *
 * @author Mario
 */

namespace POSComponent\Stream\UserStream;

use Nette\Application\UI\Form as Frm;
use POSComponent\Stream\BaseStream\BaseStream;
use POSComponent\PhotoRating;
use NetteExt\DaoBox;
use Nette\Http\Session;
use Nette\Security\User;
use Nette\Application\UI\Multiplier;
use POSComponent\Confirm;

class UserStream extends BaseStream {

	/** @var \POS\Model\UsersNewsDao */
	public $usersNewsDao;

	/** @var Session */
	private $session;

	/** @var User Přihlášený/odhlášený uživatel */
	private $user;

	public function __construct($data, DaoBox $daoBox, Session $session, $userData, $user = null) {
		parent::__construct($data, $daoBox, $userData);

		if (!empty($user)) {
			$this->user = $user;
		}
		$this->session = $session;
		$this->setDaos($daoBox);
	}

	private function setDaos(DaoBox $daoBox) {
		$this->usersNewsDao = $daoBox->usersNewsDao;
	}

	public function render() {
		$mode = 'mainStream';
		if ($this->getEnvironment()->isMobile()) {
			$templateName = "../UserStream/userStreamMobile.latte";
		} else {
			$templateName = "../UserStream/userStream.latte";
		}
		if (!empty($this->user)) {
			$user = $this->user;
		} else {
			$user = $this->presenter->user;
		}

		$this->template->user = $user;
		$this->renderBase($mode, $templateName);
	}

	/**
	 * možnost lajknutí uživatelské fotky na streamu
	 * @return \Nette\Application\UI\Multiplier multiplier pro dynamické vykreslení více komponent
	 */
	protected function createComponentBlockUser() {
		$presenter = $this->getPresenter();

		return new Multiplier(function ($dataItemId) use ($presenter) {
			$blockUser = new Confirm;
			$blockUser->setPresenter($presenter);
			$blockUser->setTittle("Blokovat uživatele");
			$blockUser->setMessage("Opravdu chcete zablokovat tohoto uživatele?");
			$blockUser->setBtnText("×");
			$blockUser->setBtnClass('blockUserStreamBtn');

			return $blockUser;
		});
	}

}
