<?php

/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

namespace POSComponent\Chat;

/**
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
interface ICommunicator {
	/* zpracovani prichozi zpravy
	 * data prichazeji POSTem
	 */

	public function handleSendMessage();

	/**
	 * Vyřízení žádosti o poslání nových zpráv
	 * @param int $lastid posledni zname id
	 * @param json $readedmessages pole idcek prectenych zprav
	 */
	public function handleRefreshMessages($lastId, $readedmessages);

	/**
	 * Vrati zpravy od jednoho uzivatele
	 * @param int $fromId id odesilatele
	 */
	public function handleLoadMessages($fromId);
}
