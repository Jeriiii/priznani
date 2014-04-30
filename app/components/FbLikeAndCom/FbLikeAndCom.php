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
class FbLikeAndCom extends Nette\Application\UI\Control {

	private $item;
	private $url;
	private $visible = TRUE; //viditelnost komentaru

	public function __construct($item, $url) {
		$this->item = $item;
		$this->url = $url;
//			if($confession->comment == 0){//pamatuje si zobrazeni pri reloadu - pokud by se pamatovat nemelo, porovnavat jinde, napr v render()
//				$this->visible = FALSE;
//			}
	}

	public function render() {
		$this->template->visible = $this->visible;
		$this->template->setFile(dirname(__FILE__) . '/fb.latte');
		$this->template->itemId = $this->item->id;
		$this->template->fbLink = $this->getFbLink($this->item);

		$this->template->render();
	}

	public function getConfessionTable() {
		if ($this->url == "priznani-o-sexu")
			return $this->presenter->context->createForms1();
		else
			return $this->presenter->context->createAdvices();
	}

	public function setVisibility($visibility) {
		$this->visible = $visibility;
		$this->invalidateControl();
	}

	/*
	 * vrátí část domény používané facebookem
	 */

	public function getFbLink($item) {
		$presenter = $this->getPresenter();

		if ($item->userGalleryID) {
			$imageID = $item->userGallery->lastImage->id;
			$galleryID = $item->userGallery->id;
			$link = $presenter->link("//:Profil:Galleries:image", array("imageID" => $imageID, "galleryID" => $galleryID));
		} elseif ($item->confessionID) {
			$link = $presenter->link("//:Page:confession", $item->confessionID);
		} elseif ($item->galleryID) {
			$link = $presenter->link("//:Competition:", array("galleryID" => $item->galleryID));
		}

		return $link;
	}

	public function handleLoadComment($visibility = TRUE) {
		$this->setVisibility($visibility);
	}

}

?>
