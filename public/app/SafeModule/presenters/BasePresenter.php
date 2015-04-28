<?php

namespace SafeModule;

use BaseProjectPresenter;

/**
 * Slouží pro testování.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
class BasePresenter extends BaseProjectPresenter {

	public function startup() {
		parent::startup();
		// ochrana proti spuštění instalace na ostrém serveru
		if ($this->productionMode) {
			$this->redirect("OnePage:");
		}

		$this->setLayout("layoutInstall");

		//$this->recalculateUserCategories();
	}

}
