<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 * 
 * Komponenta pro galerie soutěží a veřejné galerie které spravují administrátoři
 */

namespace POSComponent\Galleries\Images;

class UsersGallery extends BaseGallery {
    
	public function render() {
		parent::renderBaseGallery("../UsersGallery/usersGallery.latte");                
	}
        
	/**
	 * vrátí tabulku s obrázky
	 */
	private function getImages()
	{
		return $this->getPresenter()->context->createUsersImages();
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
		
		$folderPath = WWW_DIR . "/images/userGalleries/".$this->getPresenter()->context->getUser()->getId()."/" . $image->galleryID . "/";
		$imageFileName = $image->id . "." . $image->suffix;
		
		parent::removeImage($image, $folderPath, $imageFileName);
	}
        
	/**
	 * přepne na další obrázek
	 * @param type $imageID ID dalšího obrázku
	 */
	
	public function handleNext($imageID)
	{
		parent::setImage($imageID, $this->getImages());
	}

	/**
	 * přepne na předchozí obrázek
	 * @param type $imageID ID předchozího obrázku
	 */
	
	public function handleBack($imageID)
	{
		parent::setImage($imageID, $this->getImages());
	}
     
}