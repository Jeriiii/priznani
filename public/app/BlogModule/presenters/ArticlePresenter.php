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
use cssMin;
use POS\Model\BlogImageDao;
use NetteExt\Path\BlogImagePathCreator;
use POS\Model\BlogDao;

class ArticlePresenter extends \BasePresenter {

	/** @var \POS\Model\BlogDao @inject */
	public $blogDao;

	/** @var \POS\Model\BlogImageDao @inject */
	public $blogImageDao;

	/** @var \Nette\Database\Table\ActiveRow Aktuální článek. */
	public $article;

	public function renderArticle($url = null) {
		$article = $this->loadArticle($this->loadPage($url));
		$this->template->article = $article;

		$this->template->articleBefore = $this->blogDao->getArticleBefore($article->id);
		$this->template->articleAfter = $this->blogDao->getArticleAfter($article->id);

		$this->template->listPages = $this->blogDao->getReleaseArticles($article->id);
	}

	public function actionListArticles() {
		$this->setLayout('blogAdminLayout');
	}

	public function renderDefault() {
		$articlesDB = $this->blogDao->getReleaseArticles();

		$articles = array();
		foreach ($articlesDB as $art) {
			$articles[] = $this->loadArticle($art);
		}

		$this->template->articles = $articles;
		$this->template->listPages = $articles;
	}

	public function renderListArticles() {
		$this->template->articles = $this->blogDao->getAll('DESC');
	}

	public function actionEditArticle($url = null) {
		$this->setLayout('blogAdminLayout');

		if (!$this->user->isAllowed('article', 'editArticle')) {
			$this->flashMessage('Na tuto sekci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->article = $this->loadPage($url);
	}

	public function actionNewArticle() {
		$this->setLayout('blogAdminLayout');

		if (!$this->user->isAllowed('article', 'newArticle')) {
			$this->flashMessage('Na tuto sekci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->article = $this->blogDao->findLast();
	}

	/**
	 * Načte článek podle url.
	 * @param string $url Url článku.
	 * @throws BadRequestException
	 */
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

//		if ($page->access_rights != "all") {
//			$this->isAdmin();
//		}

		return $article;
	}

	/**
	 * Konvertuje MarkDown do html a donačte obrázky.
	 * @param \Nette\Database\Table\ActiveRow $articleDB Článek z db.
	 * @return ArrayHash Článek s obrázky.
	 */
	private function loadArticle($articleDB) {
		$convertor = new MarkdownToHtml();

		$article = ArrayHash::from($articleDB->toArray());

		$article->excerpt = $convertor->toHtml($article->excerpt);
		$article->text = $convertor->toHtml($article->text);

		/* načtení obrázků */
		$images = $articleDB->related(BlogImageDao::TABLE_NAME);
		$article->images = array();

		foreach ($images as $image) {
			$path = BlogImagePathCreator::getImgPath($article->id, $image->id, $image->suffix, "");

			$img = new ArrayHash;
			$img->path = $path;

			$article->images[] = $img;
		}

		return $article;
	}

	public function handleReleaseArticle($articleId) {
		$article = $this->blogDao->find($articleId);
		$article->update(array(
			BlogDao::COLUMN_RELEASE => 1
		));

		$this->flashMessage("Článek byl vydán.");
		$this->redirect("this");
	}

	public function handleDeleteArticle($articleId) {
		if (!$this->user->isAllowed('article', 'deleteArticle')) {
			$this->flashMessage('Na tuto akci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->blogDao->delete($articleId);
		$this->flashMessage('Článek byl smazán.');
		$this->redirect('Article:listArticles');
	}

	protected function createComponentEditPageForm($name) {
		$daoBox = new \NetteExt\DaoBox;

		$daoBox->blogDao = $this->blogDao;
		$daoBox->blogImageDao = $this->blogImageDao;

		return new Frm\EditArticleForm($daoBox, $this->article, $this, $name);
	}

	protected function createComponentNewPageForm($name) {
		$daoBox = new \NetteExt\DaoBox;

		$daoBox->blogDao = $this->blogDao;
		$daoBox->blogImageDao = $this->blogImageDao;

		return new Frm\NewArticleForm($this->article, $daoBox, $this, $name);
	}

	public function createComponentCssBlogAdminLayout() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/css');
		$compiler = \WebLoader\Compiler::createCssCompiler($files, WWW_DIR . '/cache/css');
		$compiler->addFileFilter(new \Webloader\Filter\LessFilter());
		$compiler->addFileFilter(function ($code, $compiler, $path) {
			return cssmin::minify($code);
		});

		$files->addFiles(array(
			'typeahead.css',
			'bootstrap-3-3/adminTheme.css',
		));

		/* nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu */
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/cache/css');
	}

}
