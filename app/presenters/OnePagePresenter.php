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
	    // TO DO
		// $dataForStream = ...
	}


	public function renderDefault()
	{
		// TO DO
	}

	protected function createComponentStream() {
		return new Stream($this->dataForStream);
	}

}
