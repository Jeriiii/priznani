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

class AdvicesPresenter extends BasePresenter
{
	
	public function actionDefault()
	{
	    
	}


	public function renderDefault()
	{
		$this->template->advices = $this->context->createForms1()
									->where("id_form", $id_form);
	}

}
