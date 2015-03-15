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

class AdverbPresenter extends BasePresenter
{

	public function beforeRender() {
		parent::beforeRender();
		$this->setSexMode();
	}

	/* bacha, id je url - tedy nazev stranky */
	
	public function actionContact()
	{
	}
	
	public function renderContact()
	{
	}
	
	protected function createComponentForm2Form($name) {
		return new Frm\Form2NewForm($this, $name);
	}

}
