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

class HomepagePresenter extends BasePresenter
{
	public function actionDefault() {
		$this->redirect(":OnePage:");
	}
}
