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

class OnePagePresenter extends BasePresenter
{
	
	public $dataForStream;
        
	public function actionDefault()
	{
            $this->dataForStream = $this->context->createStream();
	}


	public function renderDefault()
	{
		// TO DO            
      //      $this->dataForStream = $this->context->createStream()->limit(3);
      //      $this->template->stream = $this->dataForStream;
	}

	protected function createComponentStream() {
		return new Stream($this->dataForStream);
	}
        
        
        public function createComponentJs()
        {
                $files = new \WebLoader\FileCollection(WWW_DIR . '/js');                                       
                $files->addRemoteFile('http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js');
                $files->addFiles(array(
                    'stream.js'));
                $compiler = \WebLoader\Compiler::createJsCompiler($files, WWW_DIR . '/jstemp');
                $compiler->addFilter(function ($code) {
                    $packer = new JavaScriptPacker($code, "None");
                    return $packer->pack();
                });                
            return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/jstemp');
        }

}
