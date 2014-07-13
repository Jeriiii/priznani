<?php

namespace ProfilModule;

use Nette\Application\UI\Form as Frm,
	Nette\ComponentModel\IContainer;
use Nette\Security\User;
use POSComponent\Galleries\UserGalleries\MyUserGalleries;

class EditPresenter extends ProfilBasePresenter {

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;
        
        /**
	 * @var \POS\Model\CoupleDao
	 * @inject
	 */
	public $coupleDao;
        
        /**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
        public $userPropertyDao;
        
        /**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
        public $userGalleryDao;

	public function startup() {
		parent::startup();
		$user = $this->getUser();

		if (!$user->isLoggedIn()) {
			if ($user->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
			}
			$backlink = $this->backlink();
			$this->redirect(':Sign:in', array('backlink' => $backlink));
		} else { //kontrola opravnění pro vztup do příslušné sekce
			if (!$user->isAllowed($this->name, $this->action)) {
				$this->flashMessage('Nejdříve se musíte přihlásit.', 'warning');
				$this->redirect(':Homepage:');
			}
		}
	}

	public function renderDefault($id) {
		if (empty($id)) {
			$id = $this->getUser()->getId();
		}
		$user = $this->userDao->find($id);
		$this->template->userData = $user;
	}

	protected function createComponentFirstEditForm($name) {
		return new Frm\DatingEditFirstForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentSecondEditForm($name) {
		return new Frm\DatingEditSecondForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentThirdEditManForm($name) {
		return new Frm\DatingEditManThirdForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentThirdEditWomanForm($name) {
		return new Frm\DatingEditWomanThirdForm($this->userPropertyDao, $this->userDao, $this, $name);
	}

	protected function createComponentFourthEditWomanForm($name) {
		return new Frm\DatingEditWomanFourthForm($this->coupleDao, $this->userDao, $this, $name);
	}

	protected function createComponentFourthEditManForm($name) {
		return new Frm\DatingEditManFourthForm($this->coupleDao, $this->userDao, $this, $name);
	}

	protected function createComponentInterestedInForm($name) {
		return new Frm\InterestedInForm($this->userPropertyDao, $this->userDao, $this, $name);
	}
        
        protected function createComponentMyUserGalleries($name) {
                return new MyUserGalleries($this->userDao, $this->userGalleryDao, $this, $name);
        }

}
