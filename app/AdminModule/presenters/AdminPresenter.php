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
	Nette\DateTime,
	POS\Model\UserDao,
	POS\Grids\UsersGrid;

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
		$unconfirmedPag = $this["unconfirmedPaginator"]->getPaginator();
		$userPag = $this["userPaginator"]->getPaginator();
		$adminPag = $this["adminPaginator"]->getPaginator();
		$superadminPag = $this["superadminPaginator"]->getPaginator();

		$this->template->unconfirmed_users = $this->userDao->getInRoleUnconfirmedLimit($unconfirmedPag->itemsPerPage, $unconfirmedPag->offset);
		$this->template->users = $this->userDao->getInRoleUsersLimit($userPag->itemsPerPage, $userPag->offset);
		$this->template->admins = $this->userDao->getInRoleAdminLimit($adminPag->itemsPerPage, $adminPag->offset);
		$this->template->superadmins = $this->userDao->getInRoleSuperadminLimit($superadminPag->itemsPerPage, $superadminPag->offset);
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
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $this->userDao->getInRoleSuperadmin()->count();
		$paginator->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování adminů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentAdminPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $this->userDao->getInRoleAdmin()->count();
		$paginator->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování uživatelů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentUserPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $this->userDao->getInRoleUsers()->count();
		$paginator->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta pro stránkování nepotvrzených uživatelů
	 * @param type $name
	 * @return \VisualPaginator
	 */
	protected function createComponentUnconfirmedPaginator($name) {
		$vp = new \VisualPaginator($this, $name);
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $this->userDao->getInRoleUnconfirmed()->count();
		$paginator->itemsPerPage = self::PAGINATOR_ITEMS_PER_PAGE;
		return $vp;
	}

	/**
	 * Komponenta grido vykresluje přehledně tabulku uživatelů s účty
	 * @param type $name
	 */
	protected function createComponentUsersGrid($name) {
		return new UsersGrid($this->userDao, $this, $name);
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