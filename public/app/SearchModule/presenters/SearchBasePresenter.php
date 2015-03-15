<?php

namespace SearchModule;

/**
 * Base presenter for all profile application presenters.
 */
class SearchBasePresenter extends \BasePresenter {

	public function startup() {
		parent::startup();

		$this->checkLoggedIn();
	}

}
