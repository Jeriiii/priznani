<?php

namespace BlogModule;

/**
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
use Nette\Application\UI\Form as Frm;
use Michelf\MarkdownToHtml;
use Nette\Application\BadRequestException;
use Nette\ArrayHash;

class ArticlePresenter extends \BasePresenter {

	/** @var \POS\Model\BlogDao @inject */
	public $blogDao;

	/** @var \Nette\Database\Table\ActiveRow Aktuální článek. */
	public $article;

	/** @var \Nette\Database\Table\Selection Seznam stránek.  */
	private $listPages;

	public function actionDefault($url = null) {
		$this->loadPage($url);
	}

	public function renderDefault($url = null) {
		$convertor = new MarkdownToHtml();

		$article = ArrayHash::from($this->article->toArray());

		$article->excerpt = $convertor->toHtml($article->excerpt);
		$article->text = $convertor->toHtml($article->text);

		$this->template->article = $article;

		$this->template->listPages = !empty($this->listPages) ? $this->listPages : null;
		$this->template->isHomepage = $this->article->homepage == 1 ? TRUE : FALSE;
	}

	public function renderListArticles() {
		$this->template->pages = $this->context->createPages()
			->where("homepage", 0);
	}

	public function actionEditArticle($url = null) {
		if (!$this->user->isAllowed('article', 'editArticle')) {
			$this->flashMessage('Na tuto sekci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->loadPage($url);
	}

	public function actionNewArticle() {
		if (!$this->user->isAllowed('article', 'newArticle')) {
			$this->flashMessage('Na tuto sekci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->article = $this->blogDao->findLast();
	}

	private function loadPage($url) {
		if (empty($url)) {
			/* homepage */

			$article = $this->blogDao->findHomepage();

//			if (!$this->getUser()->isAllowed("adminDocumentation")) {
//				$this->listPages->where("access_rights", "all");
//			}
		} else {
			/* normal page */
			$article = $this->blogDao->findByUrl($url);


			if (empty($article)) {
				throw new BadRequestException('Stránka nenalezena.');
			}
		}

		$this->listPages = $this->blogDao->getListMages();

//		if ($page->access_rights != "all") {
//			$this->isAdmin();
//		}

		$this->article = $article;
	}

	public function handleDeleteArticle($articleId) {
		if (!$this->user->isInRole('admin')) {
			$this->flashMessage('Na tuto akci nemáte dostatečné oprávnění.');
			$this->redirect('Article');
		}

		$this->blogDao->delete($articleId);
		$this->flashMessage('Článek byl smazán.');
	}

	protected function createComponentEditPageForm($name) {
		return new Frm\EditArticleForm($this->article, $this, $name);
	}

	protected function createComponentNewPageForm($name) {
		return new Frm\NewArticleForm($this->article, $this->blogDao, $this, $name);
	}

}
