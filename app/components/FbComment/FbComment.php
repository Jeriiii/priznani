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

class FbComment extends Nette\Application\UI\Control
{
    private $confessionId;
	private $url;
        
        private $visible = TRUE;//viditelnost komentaru

	public function __construct($confession, $url)
    {
        $this->confessionId = $confession->id;
		$this->url = $url;
			if($confession->comment == 0){//pamatuje si zobrazeni pri reloadu - pokud by se pamatovat nemelo, porovnavat jinde, napr v render()
				$this->visible = FALSE;
			}
	}

	public function render()
	{
        $this->template->visible = $this->visible;
		$this->template->setFile(dirname(__FILE__) . '/fbComment.latte');
		$this->template->confessionId = $this->confessionId;
		$this->template->fb = $this->getFb();
		
		$this->template->render();
	}
	
	public function getConfessionTable()
	{
		if($this->url == "priznani-o-sexu")
			return $this->presenter->context->createForms1();
		else
			return $this->presenter->context->createAdvices();
	}
        
        public function setVisibility($visibility){
            $this->visible = $visibility;
            $this->invalidateControl();
        }
	
	/*
	 * vrátí část domény používané facebookem
	 */
	
	public function getFb()
	{
		if($this->url == "priznani-o-sexu")
			return "confession";
		else
			return "advice";
	}

	public function handleLoadComment($visibility = TRUE) {
                    $this->setVisibility($visibility);
	}
	
}
?>
