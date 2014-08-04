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
	/* zpracovani prichozi zpravy */

	public function handleSendMessage();

	/* zpracovani pozadavku na nove zpravy */

	public function handleRefreshMessages();
}
