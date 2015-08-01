<?php

namespace MagazineModule;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use Nette\Application\UI\Form as Frm;
use Michelf\MarkdownToHtml;

class ArticlePresenter extends \BasePresenter {

	/** @var \POS\Model\BlogDao @inject */
	public $blogDao;

	/** @var \Nette\Database\Table\ActiveRow Aktuální stránka. */
	public $page;

	/** @var \Nette\Database\Table\Selection Seznam stránek.  */
	private $listPages;

	public function actionDefault($url = null) {
		$this->loadPage($url);
	}

	public function renderDefault($url = null) {
		$convertor = new MarkdownToHtml();

		$this->template->name = $this->page->name;
		$this->template->text = $convertor->toHtml($this->page->text);
		$this->template->url = $this->page->url;
		$this->template->listPages = !empty($this->listPages) ? $this->listPages : null;
		$this->template->isHomepage = $this->page->homepage == 1 ? TRUE : FALSE;
	}

	public function renderListPages() {
		$this->template->pages = $this->context->createPages()
			->where("homepage", 0);
	}

	public function actionEditPage($url = null) {
		$this->loadPage($url);
	}

	public function actionNewPage() {
		$this->page = $this->context->createPages()
			->order("order DESC")
			->fetch();
	}

	private function loadPage($url) {
		if (empty($url)) {
			/* homepage */

			$page = $this->blogDao->findHomepage();

			$this->listPages = $this->blogDao->getListMages();

//			if (!$this->getUser()->isAllowed("adminDocumentation")) {
//				$this->listPages->where("access_rights", "all");
//			}
		} else {
			/* normal page */
			$page = $this->context->createPages()
				->where("url", $url)
				->fetch();

			if (empty($page)) {
				$this->redirect("Error:404");
			}
		}

		if ($page->access_rights != "all") {
			$this->isAdmin();
		}

		$this->page = $page;
	}

	public function handleDeletePage($idPage) {
		$this->context->createPages()
			->find($idPage)
			->delete();

		$this->flashMessage('Stránka byla smazána.');
	}

	protected function createComponentEditPageForm($name) {
		return new Frm\EditPageForm($this, $name);
	}

	protected function createComponentNewPageForm($name) {
		return new Frm\NewPageForm($this, $name);
	}

}
