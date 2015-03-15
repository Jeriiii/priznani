<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Helper pro zobrazení otazníčku nad který když najedete, zobrazí se vám nápověda
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

use Nette\Utils\Html;

class TooltipHelper {
	/* názvy helperů */

	const TOOL_TIP = "toolTip";

	/**
	 * Vypíše více info u otazníčku.
	 * @param string $infoText Text u elementu.
	 * @param string $infoEl Element, na který se má info navázat.
	 * @return Nette\Utils\Html
	 */
	public function createToolTip($infoText, $infoEl = "?") {
		$info = Html::el("div", array("class" => "tooltip-element"));
		$info->setHtml($infoText);
		$sign = Html::el("span", array("class" => "tooltip-sign"));
		$sign->setHtml($infoEl);
		$sign->add($info);

		return $sign;
	}

}
