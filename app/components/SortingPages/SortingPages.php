<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sorting
 *
 * @author Petr Kukral
 */

use Nette\Application\UI\Control;

class SortingPages extends Control{
	
	private $pages;
	
	public function __construct($data_pages)
	{
		$this->pages = new	Pages($data_pages);
	}
	
	public function render()
	{
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/poll.phtml');
		// vložíme do šablony nějaké parametry
		$template->param = $value;
		// a vykreslíme ji
		$template->render();
	}
	
	public function handlegoUp($id)
	{
		
	}
	
	public function handlegoDown($id)
	{
		
	}
	
	public function getPages()
	{
		
	}
}

?>
