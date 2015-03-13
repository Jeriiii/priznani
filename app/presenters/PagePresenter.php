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
use POSComponent\Comments\ConfessionComments;

class PagePresenter extends BasePresenter {

	public $id_page;
	public $id_form;
	public $id_advice;
	public $id_confession;
	public $text;
	public $url;
	public $page_number;
	public $confessions;
	private $page;

	/**
	 * @var \POS\Model\PartyDao
	 * @inject
	 */
	public $partyDao;

	/**
	 * @var \POS\Model\ConfessionDao
	 * @inject
	 */
	public $confessionDao;

	/**
	 * @var \POS\Model\AdviceDao
	 * @inject
	 */
	public $adviceDao;

	/**
	 * @var \POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	/**
	 * @var \POS\Model\GalleryDao
	 * @inject
	 */
	public $galleryDao;

	/**
	 * @var \POS\Model\LikeConfessionDao
	 * @inject
	 */
	public $likeConfessionDao;

	/**
	 * @var \POS\Model\LikeConfessionCommentDao
	 * @inject
	 */
	public $likeConfessionCommentDao;

	/**
	 * @var \POS\Model\CommentConfessionsDao
	 * @inject
	 */
	public $commentConfessionsDao;

	public function startup() {
		parent::startup();

		$this->setLayout("pageLayout");
	}

	public function setPaginatorForThread($order) {
		$duplicate = FALSE; //zobrazuje duplicitni priznani

		if ($order == "likes")
			$orderBy = "fblike";
		elseif ($order == "comments")
			$orderBy = "comment";
		elseif ($order == "news")
			$orderBy = "release_date";
		else
			$orderBy = NULL;

		/* stránkování */
		$confessions = $this->getDao()
			->getPublishedConfession($orderBy, $duplicate);

		/* cachovani */
		$cache = new Cache($this->context->cacheStorage, 'confession_temp');
		$countOfItems = $cache->load("count_of_confession");
		if ($countOfItems === NULL) {
			$countOfItems = $confessions->count("id");
			$cache->save("count_of_confession", $countOfItems, array(Cache::EXPIRE => '+ 1 minutes'));
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

	public function actionDefault($url, $order) {
		$this->redirect(":OnePage:");
		$this->url = $url;
		$this->setMode();
		$this->template->url = $url;
		$this->setPaginatorForThread($order);
	}

	public function renderDefault($url, $order) {
		$cz_name_for_order = array(
			"" => "nejaktivnější",
			"active" => "nejaktivnější",
			"news" => "nejnovější",
			"likes" => "nejoblínenější",
			"comments" => "nejkomentovanější"
		);
		/* kontrola, zda prislo zname razeni */
		if (array_key_exists($order, $cz_name_for_order))
			$this->template->order = $cz_name_for_order[$order];
		else
			$this->template->order = "";

		$user = $this->getUser();


		$this->includeThread($this, $order);
	}

	public function actionNaturalScience() {
		$this->flashMessage("Videa Přírodovědy s Alex již nejsou na Datenode");
		$this->redirect("Page:metro");
	}

	public function actionSimpleForm($url) {
		$this->setLayout('simpleLayout');
		$this->url = $url;
		$this->template->url = $url;
	}

	public function renderMetro() {
		$items[1]["imageUrl"] = "images/metro/interande.jpg";
		$items[1]["link"] = $this->link("Page:interactive-date");
		$items[1]["name"] = "Interaktivní rande";

		$items[2]["imageUrl"] = "images/galleries/5/79.JPG";
		$items[2]["link"] = $this->link("Competition:list");
		$items[2]["name"] = "Soutěže";

		$this->template->items = $items;
	}

	public function actionConfession($id) {
		$this->id_confession = $id;
		$confession = $this->getDao()->find($id);
		if (empty($confession)) {
			$this->flashMessage("Přiznání nebylo nalezeno.");
			$this->redirect("Page:", array("url" => "priznani-o-sexu"));
		}

		/* další přiznání */
		$this->template->otherConfession = $this->getOtherConfession($confession->id);

		$this->template->confession = $confession;
	}

	/* vrátí další přiznání/poradnu a pohlídá, aby nebylo stejné jako je zobrazované */

	public function getOtherConfession($idConfession) {
		$confessions = $this->getDao()
			->getPublishedConfession("release_date");
		foreach ($confessions as $otherConfesion) {
			if ($idConfession != $otherConfesion->id) { //nejde o totožná přiznání
				$this->template->otherConfession = $otherConfesion;
				return $otherConfesion;
			}
		}
	}

	public function renderConfession($id) {
		$this->template->now = new Nette\DateTime();
	}

	public function actionPartyConfession($id) {
		$this->id_confession = $id;
		$confession = $this->getDao()->find($id);
		if (empty($confession)) {
			$this->flashMessage("Přiznání nebylo nalezeno.");
			$this->redirect("Page:", array("url" => "priznanizparby"));
		}

		/* další přiznání */
		$this->template->otherConfession = $this->getOtherConfession($confession->id);

		$this->template->confession = $confession;
	}

	public function renderPartyConfession($id) {
		$this->template->now = new Nette\DateTime();
	}

	public function actionAdvice($id) {
		$this->url = "poradna-o-sexu";
		$this->setAdviceMode();
		$this->id_advice = $id;
		$advice = $this->adviceDao->find($id);
		if (empty($advice)) {
			$this->flashMessage("Otázka nebyla nalezena.");
			$this->redirect("Page:", array("url" => "poradna-o-sexu"));
		}

		/* další rada */
		$this->template->otherAdvice = $this->getOtherConfession($advice->id);

		$this->template->advice = $advice;
	}

	public function renderAdvice($id) {
		$this->template->now = new Nette\DateTime();
	}

	public function renderAdminScore() {
		$this->template->admins = $this->userDao->getAdminScore();
	}

	/*
	 * z databaze se nacita zatim jen form verze 1 tak bacha
	 */

	public function includeThread($presenter, $order) {
		$template = $presenter->template;
		$session = $this->context->session;

		$template->confessions = $this->confessions;

		/* sečna ankety */
		$polls = $session->getSection('polls');

		$template->polls = $polls;

		/* je na první stránce */
		if (empty($this->page) || $this->page == "1") {
			$news[] = array();
			if ($this->advicemode || $this->partymode) {
				$news["confession"] = $this->confessionDao->findLastPublishedConfession();
			} else {
				$news["advice"] = $this->adviceDao->findLastPublishedConfession();
			}
			if ($this->advicemode || $this->sexmode) {
				$news["competition"] = $this->galleryDao->findByMode("sexmode");
			}
			if ($this->partymode) {
				$news["competition"] = $this->galleryDao->findByMode("partymode");
			}
		} else {
			$news = NULL;
		}

		$this->template->news = $news;

		/* datum vydání a aktuální datum */
		$this->template->now = new Nette\DateTime();
		$time_of_release = $presenter->getDao()
			->getConfessionRelease();
		if (!empty($time_of_release)) {
			$this->template->time_of_release = $time_of_release
				->release_date;
		} else {
			$this->template->time_of_release = NULL;
		}
	}

	/**
	 * vrati spravnou tabulku bud priznani nebo poradny
	 */
	public function getDao() {
		return $this->confessionDao;
	}

	/* pro vypsani vice priznani */

	protected function createComponentPollsControl() {
		$confessions = $this->confessions;

		$dao = $this->getDao();

		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions, $dao ) {
			return new Polly($confessions[$confessionId], $dao);
		});
	}

	/* pro jedno priznani */

	protected function createComponentPollyControl() {
		if (!empty($this->id_confession))
			$id = $this->id_confession;
		else
			$id = $this->id_advice;

		$confession = $this->getDao()->find($id);

		return new Polly($confession, $this->getDao());
	}

	/* pro vypsani vice priznani */

	protected function createComponentFbCommentsControl() {
		$confessions = $this->confessions;

		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions) {
			return new FbComment($confessions[$confessionId]);
		});
	}

	/* pro vypsani vice priznani */

	protected function createComponentAddToFBPagesControl() {
		$confessions = $this->getDao()->getPublishedConfession();

		return new Nette\Application\UI\Multiplier(function ($confessionId) use ($confessions) {
			return new AddToFBPage($confessions[$confessionId]);
		});
	}

	/* pro jedno priznani */

	protected function createComponentAddToFBPageControl() {
		if (!empty($this->id_confession))
			$id = $this->id_confession;
		else
			$id = $this->id_advice;

		$confession = $this->getDao()->find($id);

		return new AddToFBPage($confession, $this->url);
	}

	public function handleincLike($id_confession) {
		$this->getDao()
			->incLike($id_confession);
	}

	public function handledecLike($id_confession) {
		$this->getDao()
			->decLike($id_confession);
	}

	public function handleincComment($id_confession) {
		$this->getDao()
			->incComment($id_confession);
	}

	public function handledecComment($id_confession) {
		$this->getDao()
			->decComment($id_confession);
	}

	protected function createComponentPartyConfessionForm($name) {
		return new Frm\PartyConfessionForm($this, $name);
	}

	protected function createComponentLikes() {
		$id = $this->getParameter("id");
		$confession = $this->confessionDao->find($id);

		return new \POSComponent\BaseLikes\ConfessionLikes($this->likeConfessionDao, $confession, $this->presenter->user->id);
	}

	/**
	 * Komponenta pro komentování obrázků
	 * @return \POSComponent\Comments\ImageComments
	 */
	protected function createComponentComments() {
		$id = $this->getParameter("id");
		$confession = $this->confessionDao->find($id);

		$confessionComment = ConfessionComments($this->likeConfessionCommentDao, $this->commentConfessionsDao, $confession);
		$confessionComment->setPresenter($this);
		return $confessionComment;
	}

}
