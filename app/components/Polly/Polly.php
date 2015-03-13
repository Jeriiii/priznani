<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use POSComponent\BaseProjectControl;

/**
 * Description of Polly
 *
 * @author Petr
 */
class Polly extends BaseProjectControl {

	private $confession;
	/* aktuální DAO, se kterým se pracuje. Buď některé z DAO pro přiznání, nebo poradna */
	private $currentConfDao;

	public function __construct($confession, $currentConfDao) {
		$this->confession = $confession;
		$this->currentConfDao = $currentConfDao;
	}

	public function render() {
		$this->template->setFile(dirname(__FILE__) . '/polly.latte');
		$this->template->confession = $this->confession;
		$polls = $this->getSession();
		$this->template->polls = $polls;
		$this->template->render();
	}

	public function getSession() {
		$session = $this->presenter->context->session;
		return $session->getSection('polls');
	}

	public function handleChangePolly($id_confession, $polly, $change) {

		if (!empty($change)) {
			if ($change != $polly) {
				$this->chooseInc($id_confession, $polly, TRUE);
			} else {
				$this->chooseInc($id_confession, $polly, FALSE, TRUE);
			}
		} else {
			$this->chooseInc($id_confession, $polly, FALSE);
		}
		if ($this->presenter->isAjax()) {
			$this->confession = $this->currentConfDao->find($id_confession);
			$this->invalidateControl();
		}
	}

	public function chooseInc($id_confession, $polly, $change, $dec = FALSE) {
		$confession = $this->currentConfDao->find($id_confession);

		$session_polly = $this->getSession();

		if ($dec) {
			if ($polly == "real") {
				$this->decReal($confession, $confession->real);
				$session_polly[$id_confession] = NULL;
			} else {
				$this->decFake($confession, $confession->fake);
				$session_polly[$id_confession] = NULL;
			}
		} else {
			if ($polly == "real") {
				$this->incReal($confession, $confession->real);
				$session_polly[$id_confession] = "real";
				if ($change == TRUE) {
					$this->decFake($confession, $confession->fake);
				}
			} else {
				$this->incFake($confession, $confession->fake);
				$session_polly[$id_confession] = "fake";
				if ($change == TRUE) {
					$this->decReal($confession, $confession->real);
				}
			}
		}
	}

	public function incReal($confession, $real) {
		$confession->update(array(
			"real" => ($real + 1)
		));
	}

	public function incFake($confession, $fake) {
		$confession->update(array(
			"fake" => ($fake + 1)
		));
	}

	public function decReal($confession, $real) {
		$confession->update(array(
			"real" => ($real - 1)
		));
	}

	public function decFake($confession, $fake) {
		$confession->update(array(
			"fake" => ($fake - 1)
		));
	}

}

?>
