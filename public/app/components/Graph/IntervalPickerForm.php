<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace Nette\Application\UI\Form;

use Nette\Forms\Form;
use POSComponent\Graph;
use Nette\DateTime;

/**
 * Formulář pro výběr časové intervalu, ve kterém se má graf zobrazit.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class IntervalPickerForm extends BaseForm {

	/** @var Graph Nad5ayen8 komponenta graf */
	private $graph;

	public function __construct(DateTime $fromDate, DateTime $toDate, $interval, $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);

		$this->graph = $parent;

		$from = $this->addDatePicker('from', "OD");
		$to = $this->addDatePicker('to', "DO");

		$from->addConditionOn($this['to'], ~Form::FILLED)
			->addRule(~Form::FILLED, 'Vyplňte obě pole OD - DO');

		$to->addConditionOn($this['from'], ~Form::FILLED)
			->addRule(~Form::FILLED, 'Vyplňte obě pole OD - DO');

		$this->addSelect("interval", "Po", array(
			Graph::INTERVAL_DAILY => "dnech",
			Graph::INTERVAL_MONTHLY => "měsících"
		));

		$this->setDefaults(array(
			'from' => $fromDate,
			'to' => $toDate,
			'interval' => $interval
		));

		$this->addSubmit('send', 'Vybrat');
		$this->setBootstrapRender();
		$this->getElementPrototype()->class('graphForm');
		$this->onSuccess[] = callback($this, 'submitted');
		return $this;
	}

	public function submitted(IntervalPickerForm $form) {
		$values = $form->getValues();

		if (!empty($values->from) && !empty($values->to)) {
			$from = new DateTime($values->from);
			$to = new DateTime($values->to);

			$this->graph->redirect("setInterval!", array(
				"from" => $from->format('Y-m-d'),
				"to" => $to->format('Y-m-d'),
				"interval" => $values->interval
			));
		} else {
			$this->graph->redirect("setInterval!", array(
				"interval" => $values->interval
			));
		}
	}

}
