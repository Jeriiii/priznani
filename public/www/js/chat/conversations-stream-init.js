/* 
 * 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */

;$(document).ready(function(){
	$("body").stream({
		ajaxLocation: '#next-conversations-btn',
		addoffset: 30,
		offsetName: "valChatMessages-offset",
		msgElement: ".stream-info-message",
		msgText: "Žádné předchozí zprávy nebyly nalezeny."
	});
	
});


