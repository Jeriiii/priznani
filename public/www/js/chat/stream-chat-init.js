/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

/**
 * Plugin pro chat jedno okno = jedna šablona. Funguje jak na konverzace tak
 * na zprávy mezi uživateli.
 *
 * @author ${Petr Kukrál a Jan Kotalík}
 */

;
(function($) {

	/**
	 * Navázání pluginu do jquery
	 * @param {Object} options
	 * @returns {Object} Instance pluginu.
	 */
	$.fn.chatConversationPage = function(options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		
		/* Aby jsme mohli plugin použít i na více prvků. */
		return this.each(function() {
			init(opts);
		});
	};
	
	$.fn.chatConversationPage.defaults = {

	};
	
	function init(opts) {
		$(document).ready(function () {
			pushOnEnd();
			getNewMessages(4000);
		});
	}

	
	
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

})(jQuery);
