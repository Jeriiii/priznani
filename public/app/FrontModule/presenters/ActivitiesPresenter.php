<?php

/**
 * ActivitiesPresenter presenter pro zobrazování aktivit
 */
class ActivitiesPresenter extends BasePresenter {

	public function actionDefault() {
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect("Sign:in");
		}
	}

	public function renderDefault() {

	}

}
