<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pages
 *
 * @author Petr
 */
class PagesSort {
	
	private $first;
	private $presenter;
	
	public function __construct($presenter) {
		$this->presenter = $presenter;
	}
	
	public function setDataPages($data_pages)
	{
		$pageUp = NULL;
		
		foreach($data_pages as $page)
		{
			$pageN = new Page();
			$pageN->data = $page;
			$pageN->pageUp = $pageUp;
			if(!isset($pageUp))
				$this->first = $pageN;
			if(isset($pageUp))
			{
				$pageUp->pageDown = $pageN;
			}
			$pageUp = $pageN;
		}
		
		//echo $this->first->pageDown->pageDown->pageDown->data->name; die();
		
	}
	
	public function goUp($id)
	{
		$page = $this->serch($id);
		
		/* osetreni proti relaudnuti stranky s odkazem na goUp */
		if(isset($page->pageUp))
		{
			$pageUp = $page->pageUp;
			$this->change($page, $pageUp);
		}
	}
	
	public function goDown($id)
	{
		$page = $this->serch($id);
		
		/* osetreni proti relaudnuti stranky s odkazem na goDown */
		if(isset($page->pageDown))
		{
			$pageDown = $page->pageDown;
			$this->change($page, $pageDown);
		}
	}


	/**
	 * implementaci teto metody muzeme udelat ajax, kdyz misto rovnou ulozeni
	 * do databaze jen zmenim seznam a tu zmenenou cast vypisu
	 */
	
	private function change($page1, $page2)
	{
		/* nacteni predem - ochrana proti padum */
		$page1_find = $this->presenter->context->createPages()
						->find($page1->data->id);

		$page2_find = $this->presenter->context->createPages()
						->find($page2->data->id);
		
		/* samotna zmena */
		
		$page2_find 
			->update(array(
				"order" => $page1->data->order
			));
		$page1_find
			->update(array(
				"order" => $page2->data->order
			));
	}

	private function serch($id)
	{
		$page = $this->first;
		while(isset($page))
		{
			if(isset($page))
			{
				if($page->data->id == $id)
				{
					return $page;
				}
			}
			$page = $page->pageDown;
		}
		return NULL;
	}
	
	
}

?>
