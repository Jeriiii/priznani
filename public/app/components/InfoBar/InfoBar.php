<?php

use Nette\Application\UI\Control,
	\Nette\ComponentModel\IContainer;

class InfoBar extends Control
{
	
	public $series;
	
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
	}
	
	public function setSeries($series) {
		$this->series = $series;
	}
	
	public function render()
	{
		$presenter = $this->getPresenter();
		$template = $this->template;
		$template->setFile(dirname(__FILE__) . '/infobar.latte');
		
		$bar = $presenter->context->createBar()
				->getBar($this->series)
				->fetch();
		
		$template->bar = $bar;
		
		// a vykreslíme ji
		$template->render();
	}

}

?>
