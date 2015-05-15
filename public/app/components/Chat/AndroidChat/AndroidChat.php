<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Komponenta celého webového chatu na POS
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class AndroidChat extends PosChat {

	/**
	 * Vykreslení komponenty
	 */
	public function render() {
		//norender
	}

	/**
	 * Vytvoření komponenty pro samotnou komunikaci (odesílání a příjmání zpráv)
	 * @return \POSComponent\Chat\StandardCommunicator
	 */
	protected function createComponentAndroidCommunicator() {
		return new AndroidCommunicator($this->chatManager);
	}

}
