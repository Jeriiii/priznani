<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/**
 * Zaručuje, že bude komponenta použitelná pro plugin stream. Tedy
 * že bude obsahovat všechny potřebné metody, který tento plugin
 * vyžaduje.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
interface IAjaxBox {

	/**
	 * Tuto metodu zavolejte ze metody render. Nastavý data, který se mají vrátit v ajaxovém i normálním
	 * požadavku v závislosti na předaném offsetu (posunu od shora).
	 * @param int $offset Offset předaný metodou handleGetMoreData. Při vyrendrování komponenty je nula.
	 */
	public function setData($offset);

	/**
	 * Uloží předaný ofsset jako parametr třídy a invaliduje snippet s příspěvky
	 * @param int $offset O kolik příspěvků se mám při načítání dalších příspěvků z DB posunout.
	 */
	public function handleGetMoreData($offset, $limit);

	/**
	 * Vrátí název snippetu, který se má při zavolání ajaxem invalidovat
	 * @return string Název snippetu, co se má invalidovat třeba "requests"
	 */
	public function getSnippetName();
}
