<?php

namespace App\Presenters;

use Nette,
	App\Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

	protected function startup() {
		parent::startup();
//		$httpRequest = $this->context->getByType('Nette\Http\Request');
//		$uri = $httpRequest->getUrl();
//		if ($uri->host == 'priznaniosexu.cz') {
//			$httpResponse = $this->context->getByType('Nette\Http\Response');
//			$httpResponse->redirect('http://datenode.cz');
//			exit;
//		}
	}

}
