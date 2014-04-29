<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
use Nette\Application\UI\Form as Frm;

class OnePagePresenter extends BasePresenter {

	public $dataForStream;
	private $count = 0;

	public function actionDefault() {
		$this->dataForStream = $this->context->createStream()->order("id DESC");
		$this->count = $this->dataForStream->count("id");
	}

	public function renderDefault() {
		$this->template->count = $this->count;
	}

	protected function createComponentStream() {
		return new Stream($this->dataForStream);
	}

	public function createComponentJs() {
		$files = new \WebLoader\FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'stream.js',
			'nette.ajax.js'
		));
		$compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/cache/js');
	}

	public function createComponentSearch() {
		$component = new Search();
		return $component;
	}

}
