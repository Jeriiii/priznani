<?php

namespace AdminModule;

use Nette\Application\UI\Form as Frm;
use POS\Grids\NewGrid;
use POS\Model\NewsDao;
use POS\Model\UsersNewsDao;

/**
 * Správa novinek
 */
class NewsPresenter extends AdminSpacePresenter {

	/** @var \POS\Model\UsersNewsDao @inject */
	public $usersNewsDao;

	/** @var \POS\Model\NewsDao @inject */
	public $newsDao;

	/** @var \POS\Model\UserDao @inject */
	public $userDao;

	/** @var \Nette\Database\Table\ActiveRow */
	private $new;

	public function actionEdit($id) {
		$this->new = $this->newsDao->find($id);
	}

	protected function createComponentNewsNewForm($name) {
		return new Frm \ NewsNewForm($this->newsDao, $this->usersNewsDao, $this->userDao, $this, $name);
	}

	protected function createComponentNewsEditForm($name) {
		return new Frm \ NewsEditForm($this->new, $this->newsDao, $this->usersNewsDao, $this->userDao, $this, $name);
	}

	protected function createComponentNewGrid($name) {
		return new NewGrid($this->newsDao, $this, $name);
	}

	public function handleDelete($id) {
		$this->newsDao->delete($id);

		$this->flashMessage("Novinka byla smazána");
		$this->redirect("this");
	}

	/**
	 * Vydá novinku tak, že je vypíše všem uživatelům.
	 * @param int $id ID novinky
	 */
	public function handleRelease($id) {
		$new = $this->newsDao->find($id);
		if ($new->release) {
			$this->flashMessage("Novinka byla již vydaná dříve");
			$this->redirect("this");
		}

		$this->newsDao->update($id, array(
			NewsDao::COLUMN_RELEASE => 1
		));

		$users = $this->userDao->getAll();

		$this->usersNewsDao->begginTransaction();
		foreach ($users as $user) {
			$this->usersNewsDao->insert(array(
				UsersNewsDao::COLUMN_NEW_ID => $id,
				UsersNewsDao::COLUMN_USER_ID => $user->id
			));
		}
		$this->usersNewsDao->endTransaction();

		$this->flashMessage("Novinka byla vydaná");
		$this->redirect("this");
	}

}
