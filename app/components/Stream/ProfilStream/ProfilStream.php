<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProfilStream
 *
 * @author Mario
 */

namespace POSComponent\Stream\ProfilStream;

use \POSComponent\Stream\BaseStream\BaseStream;

class ProfilStream extends BaseStream {

	public function render() {
		$mode = 'profilStream';
		$templateName = "../ProfilStream/profilStream.latte";

		$this->renderBase($mode, $templateName);
	}

	public function handleGetMoreData($offset) {
		parent::handleGetMoreData($offset);
	}

}
