<?php

use Nette\Application\UI\Form as Frm;

/**
 * TempPresenter Description
 */
class UserImagesPresenter extends BasePresenter {

	/**
	 * @var \POS\Model\UserImageDao
	 * @inject
	 */
	public $userImageDao;

	/**
	 * @var \POS\Model\UserGalleryDao
	 * @inject
	 */
	public $userGalleryDao;
	public $gallery;

	public function actionDefault() {
		$this->gallery = $this->userGalleryDao->findByUser(1);
	}

	public function renderDefault() {

		$this->template->gallery = $this->gallery;
		$this->template->images = $this->userImageDao->getInGallery($this->gallery->id);
	}

	protected function createComponentInsertForm($name) {
		return new Frm\InsertUserForm($this, $name, $this->gallery->id, $this->userImageDao);
	}

}
