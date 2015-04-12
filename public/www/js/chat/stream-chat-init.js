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
		valSettingsOn : false, //zapne val. chat
		msgSettingsOn : false, //zapne klasické pos. zpráv mezi dvěma uživateli
		settings: null
	};
	
	/**
	 * Navázání akce na odeslání formuláře.
	 * @param {Object} opts Nastavení pluginu.
	 */
	$.fn.chatConversationPage.submitMessageForm = function(opts) {
		console.log("first start bind");
		var id = $('.send-msg-form').attr("id");
		console.log(id);
		$('.send-msg-form').submit(function(e) {
			console.log("start");
			$.nette.ajax({
				complete:function(data) {
					$.fn.chatConversationPage.submitMessageForm(opts);
					(function() {
					var pushFn = function(){
						return pushOnEnd();
					};
					$.nette.ajax({
						url: $("#refresh-conversation").attr('href') + "&" + opts.settings.lastId + "=" + lastId,
						success: function () {},
						error: function () {},
						complete: pushFn
					});
					}());
				}
			}, this, e);
			console.log("prevent default");
			e.preventDefault();		
		});
		console.log("last end bind");
	};
	
	//				complete: function () {
//					console.log(opts);
//					$.fn.chatConversationPage.submitMessageForm(opts);
//					console.log("bind submit");
//					$.nette.ajax({
//						url: $("#refresh-conversation").attr('href') + "&" + opts.settings.lastId + "=" + lastId
//					});
//					console.log("end bind");
//				}
	
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
			//getNewMessages(opts, 4000);
		});
		$.fn.chatConversationPage.submitMessageForm(opts);
	}

	/**
	 * Načte nové zprávy do snippetu s novými zprávami - to posléze opakuje
	 * @param { int } timeout čas opakování [ms]
	 */
	function getNewMessages(opts, timeout) {
		setTimeout(function () {
			$.nette.ajax({
				url: $("#refresh-conversation").attr('href') + "&" + opts.settings.lastId + "=" + lastId,
				success: function () {
					getNewMessages(opts,timeout);
				},
				error: function () {
					getNewMessages(opts, timeout + 200);
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



