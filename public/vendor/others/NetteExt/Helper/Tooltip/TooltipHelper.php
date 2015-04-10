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
use Nette\Utils\Strings;

class TooltipHelper {
	/* názvy helperů */

	const TOOL_TIP = "toolTip";
	const TOOL_TIP_MOBILE = "toolTipMobile";

	/**
	 * Vypíše více info u otazníčku.
	 * @param string $infoText Text u elementu.
	 * @param string $infoElText u elementu.
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

	/**
	 * NEFUNK
	 * Vypíše více informací pomocí tooltipu. Používá elementy z jQueryMobile
	 * @param string $infoText Text u elementu.
	 * @param string $infoEl Text u elementu.
	 * @return Nette\Utils\Html
	 */
	public function createToolTipMobile($infoText, $infoEl = "?") {
		$id = Strings::random(6);
		$container = Html::el('div', array('class' => 'tooltip-container'));
		$link = Html::el("a", array(
				"href" => "#popupInfo" . $id,
				"class" => "tooltip-btn ui-btn ui-alt-icon ui-nodisc-icon ui-btn-inline ui-icon-info ui-btn-icon-notext",
				"data-rel" => "popup", "data-transition" => "pop",
				"title" => $infoText
		));
		$link->setHtml($infoEl);
		$paragraph = Html::el('p', array('class' => 'tooltip-paragraph'));
		$paragraph->setHtml($infoText);
		$popup = Html::el('div', array(
				'id' => "popupInfo" . $id, 'class' => 'ui-content',
				'data-theme' => 'a',
				'style' => 'max-width: 350px;')
		);
		//$popup->data['role'] = 'popup'; /* nedostane se přes nette */
		$popup->add($paragraph);
		$container->add($link);
		$container->add($popup);
		return $container;
	}

}
