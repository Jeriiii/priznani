<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Polly
 *
 * @author Petr a Koty
 * 
 * Trida pro zobrazeni komentare, podpora skryvani a viditelnosti, nastavitelne pomoci setVisibility()
 * Pro funkcnost nutno nastavit ajax a scripty pro tlacitko -
 * <script type="text/javascript" charset="utf-8">$(".show_comments_button").live("click", function(a){ a.preventDefault();$.get(this.href);$(this).after('<div class="commentspinner"><img src="{!$basePath}/images/ajax-loader.gif" /></div>')});</script>
 * 
 * Styly pro tlacitka a spinner
 * .show_comments_button{background-image: url(https://fbstatic-a.akamaihd.net/rsrc.php/v2/yl/r/_mba7MEljSe.png);background-repeat: no-repeat;background-size: auto;background-position: -352px -495px;background-color: #5b74a8;border-color: #29447e #29447e #1a356e;cursor: pointer;display: block;font-size: 14px;font-weight: bold;padding: 8px;text-align: center;text-decoration: none;vertical-align: top;white-space: nowrap;color: white;border: 1px solid #999;border-color: #29447e #29447e #1a356e;}.commentspinner{text-align: center;padding: 20px;}
 * 
 * Nahravat:
 * Tato komponenta (bez ajaxu)
 * 
 * Pro ajax:
 * ajax-loader.gif
 * skript
 */
use Nette\Utils\Strings;

class Gallery extends Nette\Application\UI\Control {

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

	public function render() {
		$this->template->partymode = $this->partymode;
		
		$this->setBeforeAndAfterImage();
		
		$this->template->beforeImageID = $this->beforeImageID;
		$this->template->image = $this->image;
		$this->template->afterImageID = $this->afterImageID;
		
		$this->template->gallery = $this->gallery;
		
		$this->template->images = $this->images;
		
		$this->template->imageLink = $this->getPresenter()->link("this", array("imageID" => $this->image->id, "galleryID" => null));
		
		/* pro local */
		$host = $this->getPresenter()->context->httpRequest->getUrl()->host;
		if($host == "localhost")
			$this->domain = WWW_DIR;
		
		$imageLink = $this->domain . "/images/galleries/" . $this->gallery->id . "/" . $this->image->id . "." . $this->image->suffix;
		
		if($this->image->videoID != 0){
			/* je to video - nic nepočítej */
		} else {
			list($width, $height, $type, $attr) = getimagesize($imageLink);
			if($width < 1.4 * $height) {
				$this->template->changeHeight = TRUE;
				$this->template->changeWidth = FALSE;
				$this->template->padding = FALSE;
			} else {
				$this->template->changeHeight = FALSE;
				$this->template->changeWidth = TRUE;
				$ratio = $width / 700;///pomer zmenceni
				$new_height = $height / $ratio;
				$this->template->padding = (int)((500 - $new_height) / 2);
			}
		}
		
		$this->template->setFile(dirname(__FILE__) . '/gallery.latte');
		$this->template->render();
	}
	
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
	
	private function getImages()
	{
		return $this->getPresenter()->context->createImages();
	}
	
	public function handleNext($imageID)
	{
		$this->setImage($imageID);
	}
	
	public function handleBack($imageID)
	{
		$this->setImage($imageID);
	}

	public function setImage($imageID) {
		$this->image = $this->getImages()
						->find($imageID)
						->fetch();
		$this->invalidateControl();
	}
	
	protected function createComponentAddToFBPageControl()
	{
		return new AddToFBPage($imageID);
	}
        
	public function handleApproveImage($imageID)
	{
		$this->getImages()
				->find($imageID)
				->update(array(
					'approved' => '1' 
				));
		$this->setImage($imageID);
	}
	
	public function handleRemoveImage($imageID)
	{
		$image = $this->getImages()
				->where("id", $imageID)
				->fetch();
		
		$way = WWW_DIR . "/images/galleries/" . $image->galleryID . "/" . $image->id . "." . $image->suffix;
		$wayMini = WWW_DIR . "/images/galleries/" . $image->galleryID . "/mini" . $image->id . "." . $image->suffix;
		
		if( file_exists($way) )
		{
			unlink($way);
		}
		
		if( file_exists($wayMini) )
		{
			unlink($wayMini);
		}
		
		$this->getImages()
				->find($imageID)
				->delete();
		$this->setBeforeAndAfterImage();
		if(!empty($this->beforeImageID)) {
			$this->setImage($this->beforeImageID);
		}else{
			$this->setImage($this->afterImageID);
		}
	}

}

?>
