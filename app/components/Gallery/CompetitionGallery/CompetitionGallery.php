<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 * 
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries;

class CompetitionGallery extends BaseGallery {

	public function render() {
		parent::renderBaseGallery("../CompetitionGallery/competitionGallery.latte");
	}

	/**
	 * vrátí tabulku s obrázky
	 */
	private function getImages()
	{
		return $this->getPresenter()->context->createImages();
	}
        
	/**
	 * schválí obrázek
	 * @param type $imageID ID obrázku, který se má schválit
	 */
	
	public function handleApproveImage($imageID)
	{
		$this->getImages()
				->find($imageID)
				->update(array(
					'approved' => '1' 
				));
		$this->setImage($imageID);
	}
	
	/**
	 * ostranění obrázku
	 * @param type $imageID ID obrázku, který se má odstranit
	 */

	public function handleRemoveImage($imageID)
	{
		$image = $this->getImages()
				->where("id", $imageID)
				->fetch();
		
		$folderPath = WWW_DIR . "/images/galleries/" . $image->galleryID . "/";
		$imageFileName = $image->id . "." . $image->suffix;
		
		parent::removeImage($image, $folderPath, $imageFileName);
	}

}