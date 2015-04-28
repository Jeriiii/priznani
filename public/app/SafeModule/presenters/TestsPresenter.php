<?php

namespace SafeModule;

/**
 * Slouží pro testování.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
class TestsPresenter extends BasePresenter {

	public function renderDefault() {
		$this->template->qTestPath = $this->template->basePath . '/tests/qunits';
	}

}
