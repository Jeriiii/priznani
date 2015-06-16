<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Rozhraní komunikátoru chatu ke komunikaci se vzdáleným zařízením (tj. ne s klientským prohlížečem)
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface IRemoteCommunicator {

	/**
	 * Zpracovani prichozi zpravy
	 * data prichazeji POSTem
	 */
	public function handleSendMessage($toId, $text, $type);

	/**
	 * Vyřízení žádosti o poslání nových zpráv
	 * @param int $lastId Posledni zname id
	 */
	public function handleRefreshMessages($lastId);

	/**
	 * Vrati informace o konverzaci s jedním uživatelem
	 * @param int $fromId Id odesilatele
	 */
	public function handleGetSingleConversation($fromId);
}
