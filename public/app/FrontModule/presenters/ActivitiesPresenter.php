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

	public function renderMobileDefault() {
		$this->template->addOffset = MobileActivities::LIMIT_OF_ACTIVITIES;
	}

	/**
	 * Vytovří komponentu pro aktivity
	 * @return \Activities Komponenta aktivit
	 */
	protected function createComponentMobileActivities() {
		$activities = new MobileActivities($this->activitiesDao, $this->loggedUser, $this->paymentDao);
		return $activities;
	}

}
