<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm,
	Nette\Caching\Cache;

class PagePresenter extends BasePresenter
{
	public $id_page;
	public $id_form;
	public $id_advice;
	public $id_confession;
	public $text;
	public $url;
	public $page_number;
	public $confessions;
	private $page;

	public function startup() {
		parent::startup();
		
		$this->setLayout("pageLayout");
	}
	
	public function setPaginatorForThread($order) {
		$duplicate = FALSE; //zobrazuje duplicitni priznani 
		
		if($order == "likes")
			$orderBy = "fblike";
		elseif ($order == "comments")
			$orderBy = "comment";
		elseif ($order == "news")
			$orderBy = "release_date";
		else
			$orderBy = NULL;
		
		/* stránkování */
		$confessions = $this->getTable()
						->getPublishedConfession($orderBy, $duplicate);
		
		/* cachovani */
		$cache = new Cache($this->context->cacheStorage, 'confession_temp');
		$countOfItems = $cache->load("count_of_confession");
		if ($countOfItems === NULL) {
			$countOfItems = $confessions->count("id");
			$cache->save("count_of_confession", $countOfItems, array( Cache::EXPIRE => '+ 1 minutes'));
		}
		
		$vp = new VisualPaginator($this, 'vp');
		$page = $vp->page;
		$paginator = $vp->getPaginator();
		$paginator->setItemCount($countOfItems); // celkový počet položek 
		$paginator->setItemsPerPage(18); // počet položek na stránce
		$paginator->setPage($page); // číslo aktuální stránky
		$confessions
				->limit($paginator->getLength(), $paginator->getOffset());
		
		//$this->paginator = $paginator;
		$this->confessions = $confessions;
		$this->page = $page;
	}

	public function beforeRender() {
	    parent::beforeRender();
	}
	
	public function actionDefault($url, $order)
	{
		$this->url = $url;
		$this->setMode();
		$this->template->url = $url;
		$this->setPaginatorForThread($order);
	}

	public function renderDefault($url, $order)
	{
		$cz_name_for_order = array(
			"" => "nejaktivnější",
			"active" => "nejaktivnější",
			"news" => "nejnovější",
			"likes" => "nejoblínenější",
			"comments" => "nejkomentovanější" 
		);
		/* kontrola, zda prislo zname razeni */
		if(array_key_exists($order, $cz_name_for_order))
			$this->template->order = $cz_name_for_order[$order];
		else
			$this->template->order = "";
		
		$user = $this->getUser();
		
		
		$this->includeThread($this, $order);
	}
	
	public function actionSimpleForm($url)
	{
		$this->setLayout('simpleLayout');
		$this->url = $url;
		$this->template->url = $url;		
	}
	
	public function renderMetro() {
		$items[1]["imageUrl"] = "images/metro/interande.jpg";
		$items[1]["link"] = $this->link("Page:interactive-date");
		$items[1]["name"] = "Interaktivní rande";

		$items[2]["imageUrl"] = "images/metro/alex.jpg";
		$items[2]["link"] = $this->link("Page:naturalScience");
		$items[2]["name"] = "Přírodověda s Alex";

		$items[3]["imageUrl"] = "images/galleries/5/79.JPG";
		$items[3]["link"] = $this->link("Competition:list");
		$items[3]["name"] = "Soutěže";
		
		$this->template->items = $items;
	}
	
	public function actionConfession($id) 
	{
		$this->setMode("priznani-o-sexu");
		$this->id_confession = $id;
		$confession = $this->getTable()
							->find($id)
							->fetch();
		if(empty($confession)) 
		{
			$this->flashMessage("Přiznání nebylo nalezeno.");
			$this->redirect("Page:", array("url" => "priznani-o-sexu"));
		}
		
		/* další přiznání */
		$this->template->otherConfession = $this->getOtherConfession($confession->id);
		
		$this->template->confession = $confession;
	}
	
	/* vrátí další přiznání/poradnu a pohlídá, aby nebylo stejné jako je zobrazované */
	
	public function getOtherConfession($idConfession)
	{
		$confessions = $this->getTable()
				->getPublishedConfession("release_date");
		foreach($confessions as $otherConfesion)
		{
			if($idConfession != $otherConfesion->id) //nejde o totožná přiznání
			{
				$this->template->otherConfession = $otherConfesion;
				return $otherConfesion;
			}
		}
	}

	public function renderConfession($id)
	{
		$this->template->now =	new Nette\DateTime();
	}
	
	public function actionPartyConfession($id) 
	{
		//$this->url = $this->domain;
		$this->setPartyMode();
		$this->id_confession = $id;
		$confession = $this->getTable()
							->find($id)
							->fetch();
		if(empty($confession)) 
		{
			$this->flashMessage("Přiznání nebylo nalezeno.");
			$this->redirect("Page:", array("url" => "priznanizparby"));
		}
		
		/* další přiznání */
		$this->template->otherConfession = $this->getOtherConfession($confession->id);
		
		$this->template->confession = $confession;
	}
	
	public function renderPartyConfession($id)
	{
		$this->template->now =	new Nette\DateTime();
	}
	
	public function actionAdvice($id) 
	{
		$this->url = "poradna-o-sexu";
		$this->setAdviceMode();
		$this->id_advice = $id;
		$advice = $this->context->createAdvices()
					->find($id)
					->fetch();
		if(empty($advice)) 
		{
			$this->flashMessage("Otázka nebyla nalezena.");
			$this->redirect("Page:", array("url" => "poradna-o-sexu"));
		}
		
		/* další rada */
		$this->template->otherAdvice = $this->getOtherConfession($advice->id);
		
		$this->template->advice = $advice;
	}

	public function renderAdvice($id)
	{
		$this->template->now =	new Nette\DateTime();
	}
	
	public function renderAdminScore()
	{
		$this->setSexMode();
		$this->template->admins = $this->context->createUsers()
									->where("role = ? OR role = ?", "admin", "superadmin")
									->where("NOT user_name", "Jerry")
									->order("admin_score DESC");
	}

	
	public function actionNaturalScience($id)
	{
		if(!empty($id))
		{
			$videos = $this->context->createEmbedVideos()
									->where("id_serie", 1)
									->order("id DESC");
			$counter = 0;
			
			foreach($videos as $video)
			{
				if($id == $video->id)
				{
					$itemsPerPage = 3;
					$this->page_number = (($counter - $counter % $itemsPerPage) / $itemsPerPage) + 1;
					// = $
					//$this->redirectUrl($this->link("Page:naturalScience", array("vp-page" => $page)) . "#" . $id);
				}
				$counter++;
			}
		}
	}

	public function renderNaturalScience($id)
	{
		$this->setSexMode();
		
		$videos = $this->context->createEmbedVideos()
									->where("id_serie", 1)
									->order("id DESC");
		
		$vp = new VisualPaginator($this, 'vp');
		if(empty($this->page_number)) $this->page_number = $vp->page;
		$page = $this->page_number;
		$paginator = $vp->getPaginator();
		$paginator->setItemCount($videos->count("id")); // celkový počet položek 
		$paginator->setItemsPerPage(3); // počet položek na stránce
		$paginator->setPage($page); // číslo aktuální stránky
		$this->template->videos = $videos
			->limit($paginator->getLength(), $paginator->getOffset());
	}

	/*
	 * z databaze se nacita zatim jen form verze 1 tak bacha
	 */
	
	public function includeThread($presenter, $order) 
	{
		$template = $presenter->template;
		$session = $this->context->session;
		
		$template->confessions = $this->confessions;

		/* sečna ankety */
		$polls = $session->getSection('polls');

		$template->polls = $polls;
		
		/* je na první stránce */
		if(empty($this->page) || $this->page == "1")
		{
			$news[] = array();
			if($this->advicemode || $this->partymode)
			{
				$news["confession"] = $presenter->context->createForms1()
										->getPublishedConfession()
										->fetch();
			}
			else
			{
				$news["advice"] = $presenter->context->createAdvices()
										->getPublishedConfession()
										->fetch();
			}
			if($this->advicemode || $this->sexmode)
			{
				$news["competition"] = $presenter->context->createGalleries()
										->where("sexmode", 1)
										->order("id DESC")
										->fetch();
			}
			if($this->partymode)
			{
				$news["competition"] = $presenter->context->createGalleries()
										->where("partymode", 1)
										->order("id DESC")
										->fetch();
			}
		}else{
			$news = NULL;
		}

		$this->template->news = $news;
		
		/* datum vydání a aktuální datum */
		$this->template->now =	new Nette\DateTime();
		$time_of_release = $presenter->getTable()
								->getConfessionRelease();
		if(!empty($time_of_release))
		{
			$this->template->time_of_release = $time_of_release
													->release_date;
		}else {
			$this->template->time_of_release = NULL;
		}
	}

	
	/**
	 * vrati spravnou tabulku bud priznani nebo poradny
	 */
	
	public function getTable()
	{
		if($this->partymode)
		{
			return $this->context->createPartyConfessions();
		}
		elseif($this->advicemode)
		{
			return $this->context->createAdvices();
		}
		elseif($this->sexmode)
		{
			return $this->context->createForms1();
		}else{
			//chyba, nebyl vybrán žádný mod
		}
	}


	/* pro vypsani vice priznani */
	
	protected function createComponentPollsControl()
	{
		$confessions = $this->confessions;
		
		$url = $this->url;
		
		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions, $url ) {
			return new Polly($confessions[$confessionId], $url );
		});
	}

	/* pro jedno priznani */
	
	protected function createComponentPollyControl()
	{
		if(!empty($this->id_confession))
			$id = $this->id_confession;
		else
			$id = $this->id_advice;
		
		$confession = $this->getTable()
						->find($id)
						->fetch();
		
		return new Polly($confession, $this->url);
	}
	
	/* pro vypsani vice priznani */
	
	protected function createComponentFbCommentsControl()
	{
		$confessions = $this->confessions;

		$url = $this->url;
		
		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions, $url) {
			return new FbComment($confessions[$confessionId], $url);
		});
	}
	
	/* pro vypsani vice priznani */
	
	protected function createComponentAddToFBPagesControl()
	{
		$confessions = $this->getTable()
							->getPublishedConfession();

		$url = $this->url;
		
		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions, $url) {
			return new AddToFBPage($confessions[$confessionId], $url);
		});
	}

	/* pro jedno priznani */
	
	protected function createComponentAddToFBPageControl()
	{
		if(!empty($this->id_confession))
			$id = $this->id_confession;
		else
			$id = $this->id_advice;
		
		$confession = $this->getTable()
						->find($id)
						->fetch();
		
		return new AddToFBPage($confession, $this->url);
	}
	
	public function handleincLike($id_confession)
	{
		$this->getTable()
				->incLike($id_confession);
	}
	
	public function handledecLike($id_confession)
	{
		$this->getTable()
				->decLike($id_confession);
	}
	
	public function handleincComment($id_confession)
	{
		$this->getTable()
				->incComment($id_confession);
	}
	
	public function handledecComment($id_confession)
	{
		$this->getTable()
				->decComment($id_confession);
	}

	protected function createComponentForm1Form($name) {
		return new Frm\Form1NewForm($this, $name);
	}
	
	protected function createComponentPartyConfessionForm($name) {
		return new Frm\PartyConfessionForm($this, $name);
	}
	
	protected function createComponentAdviceForm($name) {
		return new Frm\AdviceForm($this, $name);
	}
	
	protected function createComponentForm2Form($name) {
		return new Frm\Form2NewForm($this, $name);
	}
	
	protected function createComponentForm3Form($name) {
		return new Frm\Form3NewForm($this, $name);
	}


}
