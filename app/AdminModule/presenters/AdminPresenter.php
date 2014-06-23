<?php

/**
 * Admin presenter.
 *
 * Obsluha administrační části systému.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm,
	Nette\Utils\Finder,
	Nette\DateTime;

class AdminPresenter extends AdminSpacePresenter {

	public $id_file;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	public function actionDefault() {
		$this->redirect("Forms:forms");
	}

	public function renderAccounts() {
		$this->template->unconfirmed_users = $this->userDao->getInRoleUnconfirmed();
		$this->template->users = $this->userDao->getInRoleUsers();
		$this->template->admins = $this->userDao->getInRoleAdmin();
		$this->template->superadmins = $this->userDao->getInRoleSuperadmin();
	}

	protected function createComponentPasswordForm($name) {
		return new Frm\passwordForm($this, $name);
	}

	public function handledeleteUser($id) {
		$this->userDao->delete($id);

		$this->flashMessage("Uživatel byl smazán.");
		$this->redirect("this");
	}

	public function handlechangeRole($id, $type) {
		$role = $this->userDao->find($id)->role;

		if (!$type) {
			if ($role == "user") {
				$this->userDao->setAdminRole($id);
			} elseif ($role == "admin") {
				$this->userDao->setUserRole($id);
			}
		} else {
			if ($role == "admin") {
				$this->userDao->setSuperAdminRole($id);
			} elseif ($role == "superadmin") {
				$this->userDao->setAdminRole($id);
			}
		}
		$this->flashMessage("Práva byla změněna.");
		$this->redirect("this");
	}

}

?>