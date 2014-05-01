<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Page
 *
 * @author Petr
 */
class Page {
	
	public $pageUp;
	public $data;
	public $pageDown;
	
	public function __construct()
	{
		$this->pageUp = NULL;
		$this->data = NULL;
		$this->pageDown = NULL;
	}
}

?>
