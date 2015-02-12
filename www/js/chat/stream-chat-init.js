/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */
/**
 * Posune posuvník okna na jeho konec
 */
function pushOnEnd() {
	$("#chat-stream #stream").scrollTop($("#chat-stream #stream").prop("scrollHeight"));/* posunutí na konec */
}

/**
 * Přidá focus na políčko zprávy
 */
function focusMessageField() {
	$("#frm-valChatMessages-messageNewForm-message").focus();/* posunutí na konec */
}

;
$(function () {


	/**
	 * Načte nové zprávy do snippetu s novými zprávami - to posléze opakuje
	 * @param { int } timeout čas opakování [ms]
	 */
	function getNewMessages(timeout) {
		setTimeout(function () {
			$.nette.ajax({
				url: $("#refresh-conversation").attr('href') + "&valChatMessages-lastId=" + lastId,
				success: function () {
					getNewMessages(timeout);
				},
				error: function () {
					getNewMessages(timeout + 200);
				}
			});
			;
		}, timeout);/* zde nastavit čas obnovování */
	}


	$(document).ready(function () {
		$("body").stream({
			addoffset: 30,
			offsetName: "valChatMessages-offset",
			msgElement: ".stream-info-message",
			msgText: "Žádné předchozí zprávy nebyly nalezeny."
		});
	});

	$(document).ready(function () {
		pushOnEnd();
		getNewMessages(4000);
	});
});
