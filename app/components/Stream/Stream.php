<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stream
 *
 * @author Petr
 */

class Stream extends Nette\Application\UI\Control
{

	public function __construct()
    {
	}

	public function render()
	{
		$this->template->setFile(dirname(__FILE__) . '/stream.latte');
		// TO DO - poslání dat šabloně
		$this->template->render();
	}
	
	/* vrací další data do streamu */
	public function handleGetMoreData() {
		
	}
}
?>
