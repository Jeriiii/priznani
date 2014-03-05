<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Search Component
 *
 * @author Mario
 */
class Search extends Nette\Application\UI\Control {

	protected $finder;

	public function __construct() {
		parent::__construct();
	}

	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/default.latte');
		$this->finder = $this->createTestFinder();
		$this->template->users = $this->finder->getUsersFromDB();

		$this->template->render();
	}

	/*       public function renderAllUsers()
	  {
	  $this->template->setFile(dirname(__FILE__) . '/allUsers.latte');
	  $this->finder = $this->createTestFinder($this->getPresenter()->getContext()->createUsers());
	  $this->template->users = $this->model->getUsersFromDB();

	  $this->template->render();
	  }
	 */

	public function handleGetMoreUsers() {

		$this->template->setFile(dirname(__FILE__) . '/allUsers.latte');
		$this->finder = $this->createTestFinder();
		$this->template->users = $this->model->getAllUsersFromDB();

		$this->redirect('this');
	}

	public function createTestFinder() {
		$users = $this->getPresenter()->getContext()->createUsers();
		return new \TestFinder($users);
	}

}

?>
