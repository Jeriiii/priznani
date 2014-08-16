<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Helper pro zobrazení uživatele se jménem a miniaturou obrázku
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

namespace NetteExt\Helper;

use \Nette\Database\Table\ActiveRow;
use Nette\Utils\Html;

class ShowUserHelper {
	/* názvy helperů */

	const NAME = "showUserMin";

	/* typy nastavení */
	const TYPE_EL_SPAN = "span";
	const TYPE_EL_TABLE = "tab";

	/** @var GetImgPathHelper */
	private $getImgPathHelper;

	/** @var array Callback na fci link */
	private $linkCallback;

	public function __construct(GetImgPathHelper $getImgPathHelper, $linkCallback) {
		$this->getImgPathHelper = $getImgPathHelper;
		$this->linkCallback = $linkCallback;
	}

	/**
	 * Vytvoří miniaturu profilové fotky užvatele, vedle něj napíše jeho jméno
	 * a celé to obalí do odkazu na Profil:Show presenter
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @param string $typeEl Typ elementu, do kterého se má prvek vykreslit
	 * @param array|null $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @return \Nette\Utils\Html
	 * @throws \Exception Typ elementu nebyl nalezen.
	 */
	public function showUserMin(ActiveRow $user, $typeEl = self::TYPE_EL_SPAN, $href = null) {
		switch ($typeEl) {
			case self::TYPE_EL_TABLE:
				return $this->createShowUserMin($user, $href);
			case self::TYPE_EL_SPAN:
				return $this->createShowUserMin($user, $href);
			default:
				throw new \Exception("Type " . $typeEl . " was not found");
		}
	}

	/**
	 * Vytvoří celý element ve spanech.
	 * @param \Nette\Database\Table\ActiveRow $user Zádnam o uživateli.
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @return \Nette\Utils\Html
	 */
	private function createShowUserMin(ActiveRow $user, $href) {
		/* profilová fotka */
		$elPhoto = $this->createPhoto("span", $user);

		/* jméno */
		$elName = Html::el("span", $user->user_name);

		/* Výsledek je celý v odkazu */
		$elLink = $this->createLink($href, $user);

		/* Spojení do výsledného elementu */
		$elLink->add($elPhoto);
		$elLink->add($elName);

		return $elLink;
	}

	/**
	 * Vytvoří profilovou fotku
	 * @param string $el Typ elementu, ko kterého se má obrázek vložit.
	 * @param \Nette\Database\Table\ActiveRow $user Zádnam o uživateli.
	 * @return \Nette\Utils\Html
	 */
	private function createPhoto($el, $user) {
		$elPhoto = Html::el($el);
		if (isset($user->profilFotoID)) {
			$img = Html::el("img");
			$src = $this->getImgPathHelper->getImgMinPath($user->profilFoto, GetImgPathHelper::TYPE_USER_GALLERY);
			$img->src($src);
			$img->alt($user->user_name);
			$img->width("80px");
			$elPhoto->add($img);
		}
		return $elPhoto;
	}

	/**
	 * Vytvoří link kolem odkazu
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @param \Nette\Database\Table\ActiveRow $user Zádnam o uživateli.
	 * @return \Nette\Utils\Html
	 */
	private function createLink($href, $user) {
		$elLink = Html::el('a');

		/* pokud není href nastavený, použije se odkaz na už. profil */
		if (empty($href)) {
			$href = array("Show:", array("id" => $user->id));
		}

		$href = $this->link($href);
		$elLink->href($href);

		return $elLink;
	}

	/**
	 * Vytváří linky stejně jako metoda link z presenteru.
	 * @param array $href Vlasní odkaz. První prvek pole je odkaz, druhý prvek je pole parametrů.
	 * @return string Výsledný odkaz.
	 */
	private function link($href) {
		return call_user_func_array($this->linkCallback, $href);
	}

}
