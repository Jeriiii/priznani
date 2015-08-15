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

class ArticlePresenter extends \BasePresenter {

	/** @var \POS\Model\BlogDao @inject */
	public $blogDao;

	/** @var \POS\Model\BlogImageDao @inject */
	public $blogImageDao;

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

		/* načtení obrázků */
		$images = $this->article->related(BlogImageDao::TABLE_NAME);
		$article->images = array();

		foreach ($images as $image) {
			$path = BlogImagePathCreator::getImgPath($article->id, $image->id, $image->suffix, $this->template->basePath);

			$img = new ArrayHash;
			$img->path = $path;

			$article->images[] = $img;
		}

		$this->template->article = $article;

		$this->template->listPages = !empty($this->listPages) ? $this->listPages : null;
	}

	public function actionListArticles() {
		$this->setLayout('blogAdminLayout');
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

		$this->loadPage($url);
	}

	public function actionNewArticle() {
		$this->setLayout('blogAdminLayout');

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
		if (!$this->user->isAllowed('article', 'deleteArticle')) {
			$this->flashMessage('Na tuto akci nemáte dostatečné oprávnění.');
			$this->redirect('Article:');
		}

		$this->blogDao->delete($articleId);
		$this->flashMessage('Článek byl smazán.');
		$this->redirect('Article:listArticles');
	}

	protected function createComponentEditPageForm($name) {
		return new Frm\EditArticleForm($this->article, $this, $name);
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
