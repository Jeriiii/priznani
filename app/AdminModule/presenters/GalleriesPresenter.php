<?php

/**
 * Form presenter.
 *
 * Obsluha administrační části systému.
 * Formuláře.
 *
 * @author     Petr Kukrál
 * @package    Safira
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm;

class GalleriesPresenter extends AdminSpacePresenter {

	public $id_gallery;

	/**
	 * @var \POS\Model\GalleryDao
	 * @inject
	 */
	public $galleryDao;

	/**
	 * @var \POS\Model\ImageDao
	 * @inject
	 */
	public $imageDao;

	/**
	 * @var \POS\Model\VideoDao
	 * @inject
	 */
	public $videoDao;

	public function renderGalleries() {
		$this->template->galleries = $this->galleryDao->getAll();
	}

	public function actionGallery($id_gallery) {
		$this->id_gallery = $id_gallery;
	}

	public function renderGallery($id_gallery) {
		$this->template->gallery = $this->galleryDao->find($id_gallery);
		$this->template->images = $this->imageDao->getInGallery($id_gallery);
	}

	protected function createComponentImageGalleryNewForm($name) {
		return new Frm\imageGalleryNewForm($this->imageDao, $this, $name);
	}

	protected function createComponentVideoGalleryNewForm($name) {
		return new Frm\videoGalleryNewForm($this->videoDao, $this->imageDao, $this, $name);
	}

	protected function createComponentGalleryNewForm($name) {
		return new Frm\galleryNewForm($this->galleryDao, $this, $name);
	}

	protected function createComponentGalleryChangeForm($name) {
		return new Frm\galleryChangeForm($this->galleryDao, $this->id_gallery, $this, $name);
	}

	protected function createComponentGalleryReleaseForm($name) {
		return new Frm\galleryReleaseForm($this, $name);
	}

	public function handledeleteGallery($id_gallery) {
		$images = $this->imageDao->getInGallery($id_gallery);

		foreach ($images as $image) {
			$this->handledeleteImage($image->id, $id_gallery, FALSE);
		}

		$way = WWW_DIR . "/images/galleries/" . $id_gallery;

		if (file_exists($way)) {
			rmdir($way);
		}

		$this->galleryDao->delete($id_gallery);

		$this->flashMessage("Galerie byla smazána.");
		$this->redirect("this");
	}

	public function handledeleteImage($id_image, $id_gallery, $redirekt = TRUE) {
		$image = $this->imageDao->find($id_image);

		$way = WWW_DIR . "/images/galleries/" . $id_gallery . "/" . $image->id . "." . $image->suffix;
		$wayGalScrn = WWW_DIR . "/images/galleries/" . $id_gallery . "/galScrn" . $image->id . "." . $image->suffix;
		$wayGalScrnMin = WWW_DIR . "/images/galleries/" . $id_gallery . "/minSqr" . $image->id . "." . $image->suffix;
		$wayMini = WWW_DIR . "/images/galleries/" . $id_gallery . "/min" . $image->id . "." . $image->suffix;

		if (file_exists($way)) {
			unlink($way);
		}

		if (file_exists($wayGalScrn)) {
			unlink($wayGalScrn);
		}

		if (file_exists($wayGalScrnMin)) {
			unlink($wayGalScrnMin);
		}

		if (file_exists($wayMini)) {
			unlink($wayMini);
		}

		$this->imageDao->delete($id_image);

		/* jedna se o video */
		if ($image->videoID != 0) {
			$this->videoDao->delete($image->videoID);
		}

		if ($redirekt) {
			$this->flashMessage("Obrázek byl smazán.");
			$this->redirect("this");
		}
	}

}

?>