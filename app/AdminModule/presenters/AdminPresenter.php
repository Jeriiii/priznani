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

	public function renderDefault() {
		$this->redirect("Forms:forms");
//		$this->template->forms_new_send = $this->context->createForm_new_send()
//												->order("date DESC");
//		$this->template->forms = $this->context->createForms();
//
//		$date = new DateTime();
//		$date->modify('-2 month');
//
//		$this->context->createForm_new_send()
//				->where("mark", 1)
//				->where("date < ?", $date)
//				->delete();
	}

	public function renderAccounts() {
		$this->template->unconfirmed_users = $this->userDao->getInRoleUnconfirmed();
		$this->template->users = $this->userDao->getInRoleUsers();
		$this->template->admins = $this->userDao->getInRoleAdmin();
		$this->template->superadmins = $this->userDao->getInRoleSuperadmin();
	}

	protected function createComponentFacebookForm($name) {
		return new Frm\facebookForm($this, $name);
	}

	protected function createComponentPasswordForm($name) {
		return new Frm\passwordForm($this, $name);
	}

	protected function createComponentMapForm($name) {
		return new Frm\mapForm($this, $name);
	}

	protected function createComponentDetailInzerat() {
		$dialog = new \Cherry\JDialogs\BaseDialog;
		$dialog->template_file = APP_DIR . "/dialogs/templates/basedialog.latte";

		$text = "muj text";

		$dialog->addData(array("text" => $text));

		$dialog->addOption(array(
			"autoOpen" => "false",
			"title" => "testDialogTwo",
		));

		return $dialog;
	}

	public function handledeleteUser($id) {
		$this->context->createUsers()
			->where("id", $id)
			->delete();

		$this->flashMessage("Uživatel byl smazán.");
		$this->redirect("this");
	}

	public function handlechangeRole($id, $type) {
		$user = $this->context->createUsers()
			->where("id", $id);
		$role = $this->context->createUsers()
				->where("id", $id)
				->fetch()
			->role;
		if (!$type) {
			if ($role == "user") {
				$user->update(array(
					"role" => "admin"
				));
			} elseif ($role == "admin") {
				$user->update(array(
					"role" => "user"
				));
			}
		} else {
			if ($role == "admin") {
				$user->update(array(
					"role" => "superadmin"
				));
			} elseif ($role == "superadmin") {
				$user->update(array(
					"role" => "admin"
				));
			}
		}
		$this->flashMessage("Práva byla změněna.");
		$this->redirect("this");
	}

	public function handledeleteFile($id) {
		$file = $this->context->createFiles()
			->where("id", $id);

		$path = WWW_DIR . "/files/page_files/" . $id . '.' . $file->fetch()->suffix;

		if (file_exists($path))
			unlink($path);

		$file->delete();
		$this->flashMessage("Soubor byl smazán.");
		$this->redirect("this");
	}

	public function handleMarkNewSendForm($id, $path) {
		$this->context->createForm_new_send()
			->find($id)
			->update(array(
				"mark" => 1
		));

		$this->redirectUrl($path);
	}

}

?>