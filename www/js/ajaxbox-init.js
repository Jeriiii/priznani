

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

;
$(document).ready(function () {
	$.convRunAjax = true;
	$.convOffset = 0;
	$.convLimit = 5;

//inicializace vyskakovacích okének
	$('#conversations').ajaxBox({
		buttonSelector: '#messages-btn',
		topMargin: -10,
		arrowOrientation: 'right',
		theme: "posAjaxBox posConversations",
		headerHtml: "Příchozí zprávy",
		loadUrl: loadConversationsLink, /* link vygenerovaný komponentou StandardConversationsList */
		dataArrived: function (opts, data) {
			if (data.snippets['snippet-chat-conversationList-conversations'].trim() == "") {
				$.convRunAjax = false;
				$('#conversations').append('<div class="noConvMessages">Žádné další zprávy.</div>');
				$('div[data-related="' + opts.buttonSelector + '"] .loadingGif').css('display', 'none');
			}
		},
		reloadPermitted: function (opts) {
			return $.convRunAjax;
		},
		dataToReload: function (opts) {
			var offset = $.convOffset;
			$.convOffset += 5;
			return {
				'chat-conversationList-offset': offset,
				'chat-conversationList-limit': $.convLimit
			};
		},
		ajaxObserverId: 'chatConversationWindow',
		observerResponseHandle: function (opts, data) {
			if (data) {
				$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
			} else {
				$(opts.buttonSelector).find('.ajaxbox-button-info').css('display', 'none');
			}
		}
	});



});
