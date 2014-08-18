/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

$(function() {
	$("#sendmessage-dialog").dialog({
		autoOpen: false,
		show: {
			effect: "fade",
			duration: 500
		},
		hide: {
			effect: "fade",
			duration: 500
		},
		width: 500
	});

	$("#sendmessage-dialog").parent().wrap('<div class="ui-chat"></div>');

	$("#sendmessage-button").click(function() {
		$("#sendmessage-dialog").dialog("open");
	});
});