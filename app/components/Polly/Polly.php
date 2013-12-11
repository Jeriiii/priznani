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

class Polly extends Nette\Application\UI\Control
{
    private $confession;
	private $url;

	public function __construct($confession, $url)
    {
        $this->confession = $confession;
		$this->url = $url;
	}

	public function render()
	{
		$this->template->setFile(dirname(__FILE__) . '/polly.latte');
		$this->template->confession = $this->confession;
		$polls = $this->getSession();
		$this->template->partymode = $this->getPresenter()->partymode;
		$this->template->polls = $polls;
		$this->template->render();
	}
	
	public function getSession() {
		$session = $this->presenter->context->session;
		return $session->getSection('polls');
	}
	
	public function getConfessionTable()
	{
		if($this->url == "priznani-o-sexu")
			return $this->presenter->context->createForms1();
		else
			return $this->presenter->context->createAdvices();
	}

	public function handleChangePolly($id_confession, $polly, $change) {	
	
		if(!empty($change)) {
			if($change != $polly) {
				$this->chooseInc($id_confession, $polly, TRUE);
			}else{
				$this->chooseInc($id_confession, $polly, FALSE, TRUE);
			}
		}else{
			$this->chooseInc($id_confession, $polly, FALSE);
		}
		if($this->presenter->isAjax()) {				
			$this->confession = $this->getConfessionTable()
								->find($id_confession)
								->fetch();
			$this->invalidateControl();
		}
	}
	
	public function chooseInc($id_confession, $polly, $change, $dec = FALSE)
	{
		$confession = $this->getConfessionTable()
						->find($id_confession)
						->fetch();
		
		$confession_update = $this->getConfessionTable()
						->find($id_confession);
		
		$session_polly = $this->getSession();
		
		if($dec){
			if($polly == "real") {
				$this->decReal($confession_update, $confession);
				$session_polly[$id_confession] = NULL;
			}else{
				$this->decFake($confession_update, $confession);
				$session_polly[$id_confession] = NULL;
			}
		}else{
			if($polly == "real") {
				$this->incReal($confession_update, $confession);
				$session_polly[$id_confession] = "real";
				if($change == TRUE) $this->decFake($confession_update, $confession);
			}else{
				$this->incFake($confession_update, $confession);
				$session_polly[$id_confession] = "fake";
				if($change == TRUE) $this->decReal($confession_update, $confession);
			}
		}
	}

	public function incReal($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"real" => ($confession->real + 1)
			));
	}
	
	public function incFake($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"fake" => ($confession->fake + 1)
			));
	}
	
	public function decReal($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"real" => ($confession->real - 1)
			));
	}
	
	public function decFake($confession_update, $confession)
	{
		$confession_update
			->update(array(
				"fake" => ($confession->fake - 1)
			));
	}
}
?>
