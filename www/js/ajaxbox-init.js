

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

;
$(document).ready(function() {
	$.convRunAjax = true;
	$.convOffset = 0;
	$.convLimit = 5;

//inicializace vyskakovacích okének
	$('#conversations').ajaxBox({
		buttonSelector: '#messages-btn',
		topMargin: -10,
		arrowOrientation: 'right',
		loadUrl: loadConversationsLink, /* link vygenerovaný komponentou StandardConversationsList */
		dataArrived: function(opts, data) {
			if (data.snippets['snippet-chat-conversationList-conversations'].trim() == "") {
				$.convRunAjax = false;
				$('div[data-related="' + opts.buttonSelector + '"] .window-info').html('Žádné další zprávy.');
				$('div[data-related="' + opts.buttonSelector + '"] .loadingGif').css('display', 'none');
			}
		},
		reloadPermitted: function(opts) {
			return $.convRunAjax;
		},
		dataToReload: function(opts) {
			var offset = $.convOffset;
			$.convOffset += 5;
			return {
				'chat-conversationList-offset': offset,
				'chat-conversationList-limit': $.convLimit
			};
		},
		ajaxObserverId: 'chatConversationWindow'
	});


	$('#contact-9904961').ajaxBox({
		buttonSelector: '#droplink'
	});

});
