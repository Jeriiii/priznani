<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 * Obecné rozhraní komunikátoru chatu
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface ICommunicator {

	/**
	 * Zpracovani prichozi zpravy
	 * data prichazeji POSTem
	 */
	public function handleSendMessage();

	/**
	 * Vyřízení žádosti o poslání nových zpráv
	 * @param int $lastId Posledni zname id
	 * @param json $readedmessages Pole idcek prectenych zprav
	 */
	public function handleRefreshMessages($lastId, $readedmessages);

	/**
	 * Vrati zpravy od jednoho uzivatele
	 * @param int $fromId Id odesilatele
	 */
	public function handleLoadMessages($fromId);
}
