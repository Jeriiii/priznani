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
		var opts = $.extend({}, $.fn.chatConversationPage.defaults, options);
		
		/* Aby jsme mohli plugin použít i na více prvků. */
		return this.each(function() {
			opts = getSettings(opts);
			init(opts);
		});
	};
	
	$.fn.chatConversationPage.defaults = {
		sendMessageLink: '',
		recipientId: null,
		valSettingsOn : false, //zapne val. chat
		msgSettingsOn : false, //zapne klasické pos. zpráv mezi dvěma uživateli
		settings: null,
		timeMessageCheck: 3000, //čas jak dlouho trvá, než se zeptá na nové zprávy
		addTimeMessageCheck: 500 //přírůstek k času, když nezachytí žádnou zprávu
	};
	
	/* nastavuje se když přišli nové zprávy */
	$.fn.chatConversationPage.newMsg = false;
	
	/**
	 * Navázání akce na odeslání formuláře.
	 * @param {Object} opts Nastavení pluginu.
	 */
	function submitMessageForm (opts) {
		$('.send-msg-form').submit(function(e) {
			e.preventDefault();
			var requestData = {
				to: opts.recipientId,
				type: 'textMessage',
				text: $(this).find('input[name="message"]').val(),
				lastid: lastId
			};
//			
//			var json = JSON.stringify(data);
//
//			$.ajax({
//				dataType: "json",
//				type: 'POST',
//				url: url,
//				data: json,
//				contentType: 'application/json; charset=utf-8',
//				success: handleResponse,
//				error: function () {
//					reloadWindowUnload();
//				}
//			});
			
//			$.nette.ajax({
////				complete:function(data, status) {
////					if(status === 'success'){
////						submitMessageForm(opts);
////						$.nette.ajax({
////							url: $("#refresh-conversation").attr('href') + "&" + opts.settings.lastId + "=" + lastId,
////							success: function () {},
////							error: function () {},
////							complete: pushOnEnd
////						});
////					}
////				}
//			}, this, e);		
		});
	}
	
	/**
	 * Vrátí správné nastavení pluginu.
	 * @param {Object} opts
	 * @returns {Object}
	 */
	function getSettings(opts) {
		if(opts.msgSettingsOn) {
			opts.settings = {
				lastId : "conversation-lastId"
			};
		} else {
			opts.settings = {
				lastId : "valChatMessages-lastId"
			};
		}
		
		return opts;
	}
	
	function init(opts) {
		$(document).ready(function () {
			pushOnEnd();
			getNewMessages(opts, opts.timeMessageCheck);
		});
		submitMessageForm(opts);
	}

	/**
	 * Načte nové zprávy do snippetu s novými zprávami - to posléze opakuje
	 */
	function getNewMessages(opts, timeout) {
		setTimeout(function () {
			$.nette.ajax({
				url: $("#refresh-conversation").attr('href') + "&" + opts.settings.lastId + "=" + lastId,
				success: function (data) {
					if (data.snippets['snippet-conversation-new-stream-messages'] == "") {//pokud snippet už neobnovuje data
						/* nepřišli žádné nové zprávy */
						if(timeout > 60000) {
							timeout = 60000;
						}
						getNewMessages(opts, timeout + opts.addTimeMessageCheck); //zvýší se čas
						console.log("nepřišli žádné nové zprávy");
					} else {
						/* přišli nové zprávy */
						getNewMessages(opts, opts.timeMessageCheck); //vynuluje se čas
						$.fn.chatConversationPage.newMsg = true;
						console.log("přišli nové zprávy");
					}
					
				},
				complete: function(data) {
					console.log($.fn.chatConversationPage.newMsg);
					if ($.fn.chatConversationPage.newMsg) {
						$.fn.chatConversationPage.newMsg = false;
						pushOnEnd();
					}
				},
				error: function () {
					getNewMessages(opts, opts.timeMessageCheck + opts.addTimeMessageCheck);
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



