<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 8.9.2015
 */

namespace Activity;

use Nette\Database\Table\Selection;
use Nette\ArrayHash;
use Nette\Utils\Html;
use Nette\Application\UI\Presenter;

/**
 * Třída na vytváření aktivit
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class Creator {

	/**
	 * @var Presenter
	 */
	private $presenter;

	public function __construct(Presenter $p) {
		$this->presenter = $p;
	}

	public function createList(Selection $sel) {
		$activities = array();

		foreach ($sel as $act) {
			$activity = ArrayHash::from($act->toArray());

			$activity->linkEl = $this->createLinkEl($activity);

			$activities[] = $activity;
		}

		return $activities;
	}

	private function createLinkEl($activity) {
		$linkEl = Html::el('a');
		$linkEl->addAttributes(array(
			'data-activity-viewed-id' => $activity->id
		));

		if ($activity->statusID != NULL) {
			$link = $this->presenter->link(':Status:', $activity->status->id);
		} elseif ($activity->imageID != NULL) {
			$link = $this->presenter->link(':Status:', $activity->status->id);
		} elseif ($activity->friendRequestID != NULL) {
			// DO NOTHING
			return NULL;
		} else { {
				include user activity => $activity
			}
		}

		$linkEl->href($link);
		return $linkEl;
	}

}
