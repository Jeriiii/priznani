<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm;

class LinkPresenter extends BasePresenter
{
	public function actionFb($id) {
		$streamItem = $this->context->createStream()
						->find($id)
						->fetch();
		if($streamItem) {
			if($streamItem->confessionID) {
				$this->forward(":Page:confession", array("id" => $streamItem->confessionID));
			} elseif($streamItem->userGalleryID) {
				$gallery = $this->context->createUsersGalleries()
								->find($streamItem->userGalleryID)
								->fetch();
				
				if($gallery->lastImageID) {
					$this->forward("Profil:Galleries:image ", 
						array(
							"imageID" => $gallery->lastImageID,
							"galleryID" => $streamItem->userGalleryID
						));
				}
			}
		}
		
		$this->flashMessage("Příspěvek nebyl nalezen");
		$this->redirect(":OnePage:");
	}
}
