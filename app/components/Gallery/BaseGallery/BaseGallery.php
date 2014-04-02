<?php

/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 * 
 * Základ komponenty, umožňující zobrazení obrázku, listování a další funkce
 */

namespace POSComponent\Galleries\Images;

class BaseGallery extends \Nette\Application\UI\Control {

	/* vsechny obrazky z galerie */
	private $images;
	/* aktualni obrazek */
	private $image;
	/* aktualni galerie */
	private $gallery;
	/* aktualni domena */
	private $domain;
	/* jsme na priznani z parby */
	private $partymode;

	private $beforeImageID;
	private $afterImageID;

	public function __construct($images, $image, $gallery, $domain, $partymode) {		
		$this->images = $images->order("id DESC");
		$this->image = $image;
		$this->gallery = $gallery;
		$this->domain = $domain;
		$this->partymode = $partymode;
	}

	public function renderBaseGallery($templateName) {
		$this->template->partymode = $this->partymode;

		$this->setBeforeAndAfterImage();

		$this->template->beforeImageID = $this->beforeImageID;
		$this->template->image = $this->image;
		$this->template->afterImageID = $this->afterImageID;

		$this->template->gallery = $this->gallery;

		$this->template->images = $this->images;

		$this->template->imageLink = $this->getPresenter()->link("this", array("imageID" => $this->image->id, "galleryID" => null));

		// rozhoduje, zda je obrázek vyšší nebo širší
		if($this->image->widthGalScrn == 700) {
			$setWidth = TRUE;
			$this->template->imgPaddingTopBottom = (525 - $this->image->heightGalScrn) / 2;
		}else{
			$setWidth = FALSE;
		}
		$this->template->setWidth = $setWidth;

		$this->template->setFile(dirname(__FILE__) . '/' . $templateName);
		$this->template->render();
	}

	/**
	 * nastavuje proměnné třídy beforeImageID a afterImageID
	 */
	
	private function setBeforeAndAfterImage() {
		$imageID = $this->image->id;
		$beforeImageID = FALSE;
		$afterImageID = FALSE;
		$setAfter = FALSE;

		foreach($this->images as $image)
		{
			if($setAfter)
			{
				$afterImageID = $image->id;
				break;
			}

			if($image->id == $imageID)
				$setAfter = TRUE; // pri dalsi obratce nastavi nasledujici prvek
			else
				$beforeImageID = $image->id; //nevyplni se pri nalezeni hledaneho obrazku
		}

		$this->beforeImageID = $beforeImageID;
		$this->afterImageID = $afterImageID;
	}

	/**
	 * vrátí tabulku s obrázky
	 */
	private function getImages()
	{
		return $this->getPresenter()->context->createImages();
	}

	/**
	 * přepne na další obrázek
	 * @param type $imageID ID dalšího obrázku
	 */
	
	public function handleNext($imageID)
	{
		$this->setImage($imageID);
	}

	/**
	 * přepne na předchozí obrázek
	 * @param type $imageID ID předchozího obrázku
	 */
	
	public function handleBack($imageID)
	{
		$this->setImage($imageID);
	}

	/**
	 * nastaví nový obrázek po přechodu doleva/doprava jako aktuální a invaliduje
	 * šablonu
	 * @param type $imageID ID obrázku který má být aktuální
	 */
	public function setImage($imageID) {
		$this->image = $this->getImages()
						->find($imageID)
						->fetch();
		$this->invalidateControl();
	}
	
	/**
	 * 
	 * @param type $imageID ID obrázku, který se má odstranit
	 */
	
	/**
	 * odstranění obrázku
	 * @param type $image záznam obrázku z tabulky, který se má odstranit
	 * @param type $folderPath cesta do složky s obrázkem
	 * @param type $imageFileName jméno souboru obrázku (bez předpony mini, sqr a pod)
	 */

	public function removeImage($image, $folderPath, $imageFileName)
	{
		$preffixs = array("", "mini", "sqr");
		
		// mazání souborů
		foreach($preffixs as $prefix) {
			$path = $folderPath . $preffix . $imageFileName;
			
			if( file_exists($path) )	{
				unlink($path);
			}
		}

		$this->getImages()
				->find($image->id)
				->delete();
		$this->setBeforeAndAfterImage();
		if(!empty($this->beforeImageID)) {
			$this->setImage($this->beforeImageID);
		}else{
			$this->setImage($this->afterImageID);
		}
	}
	
	protected function createComponentAddToFBPageControl()
	{
		return new AddToFBPage($imageID);
	}

}