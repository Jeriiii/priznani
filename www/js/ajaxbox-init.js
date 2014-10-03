

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
		dataArrived: function(data) {
			if (data.snippets['snippet-chat-conversationList-conversations'].trim() == "") {
				$.convRunAjax = false;
			}
		},
		reloadPermitted: function() {
			return $.convRunAjax;
		},
		dataToReload: function() {
			var offset = $.convOffset;
			$.convOffset += 5;
			return {
				'chat-conversationList-offset': offset,
				'chat-conversationList-limit': $.convLimit
			};
		},
		ajaxObserverId: 'chatConversationWindow'
	});
});
