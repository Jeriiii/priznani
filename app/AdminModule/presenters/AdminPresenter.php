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

	const PAGINATOR_ITEMS_PER_PAGE = 10;

	public function actionDefault() {
		$this->redirect("Forms:forms");
	}

	public function renderAccounts() {

		$adminPaginator = $this["adminPaginator"]->getPaginator();
		$adminPaginator->itemCount = $this->userDao->getInRoleAdmin()->count();

		$superadminPaginator = $this["superadminPaginator"]->getPaginator();
		$superadminPaginator->itemCount = $this->userDao->getInRoleSuperadmin()->count();

		$userPaginator = $this["userPaginator"]->getPaginator();
		$userPaginator->itemCount = $this->userDao->getInRoleUsers()->count();

		$unconfirmedPaginator = $this["unconfirmedPaginator"]->getPaginator();
		$unconfirmedPaginator->itemCount = $this->userDao->getInRoleUnconfirmed()->count();

		$this->template->unconfirmed_users = $this->userDao->getInRoleUnconfirmedForPaginator($unconfirmedPaginator->itemsPerPage, $unconfirmedPaginator->offset);
		$this->template->users = $this->userDao->getInRoleUsersForPaginator($userPaginator->itemsPerPage, $userPaginator->offset);
		$this->template->admins = $this->userDao->getInRoleAdminForPaginator($adminPaginator->itemsPerPage, $adminPaginator->offset);
		$this->template->superadmins = $this->userDao->getInRoleSuperadminForPaginator($superadminPaginator->itemsPerPage, $superadminPaginator->offset);
		$this->template->totalCount = $this->userDao->getTable()->count();
	}

	protected function createComponentPasswordForm($name) {
		return new Frm\passwordForm($this, $name);
	}

	/**
	 * Komponenta pro stránkování superadminů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentSuperadminPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$vp->getPaginator()->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování adminů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentAdminPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$vp->getPaginator()->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování uživatelů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentUserPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$vp->getPaginator()->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování nepotvrzených uživatelů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentUnconfirmedPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$vp->getPaginator()->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
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