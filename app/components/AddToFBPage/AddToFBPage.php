<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Polly
 *
 * @author Petr
 */

class AddToFBPage extends Nette\Application\UI\Control
{
    private $imageID;

	public function __construct($imageID)
    {
        $this->imageID = $imageID;
	}

	public function render()
	{
		$this->template->setFile(dirname(__FILE__) . '/addToFBPage.latte');
		$this->template->imageID = $this->imageID;
		$polls = $this->getSession();
		$this->template->polls = $polls;
		$this->template->render();
	}
	
	public function getSession() {
		$session = $this->presenter->context->session;
		return $session->getSection('add-to-fb-page');
	}
	
	public function getImage($imageID)
	{
		return $this->presenter->context
					->createImages()
					->find($imageID)
					->fetch();
	}

	public function handleChangeAdd($id_confession, $add /* TRUE = inc, FALSE = dec */) {	
	
		$this->chooseInc($id_confession, $add);
		
		if($this->presenter->isAjax()) {				
			$this->confession = $this->getImage();
			$this->invalidateControl();
		}
	}
	
	public function chooseInc($id_confession, $add /* TRUE = inc, FALSE = dec */)
	{
		$image = $this->getImage();
		
		$confession_update = $this->getImage();
		
		$session_polly = $this->getSession();
		
		if($add == "inc"){ /* inc */
			$this->incFB($confession_update, $confession);
			$session_polly[$id_confession] = "check";
		}else{ /* dec */
			$this->decFB($confession_update, $confession);
			$session_polly[$id_confession] = NULL;

		}
	}

	public function incFB($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"add_to_fb_page" => ($confession->add_to_fb_page + 1)
			));
	}
	
	public function decFB($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"add_to_fb_page" => ($confession->add_to_fb_page - 1)
			));
	}
}
?>
