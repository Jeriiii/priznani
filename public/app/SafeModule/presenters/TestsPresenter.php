<?php

namespace SafeModule;

/**
 * Slouží pro testování.
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */
class TestsPresenter extends BasePresenter {

	public function renderDefault($param) {
		$this->template->qTestPath = WWW_DIR . '/../tester/QUnits';
	}

}
