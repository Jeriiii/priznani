/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/**
 * Zmenšuje a zvětšuje okénko s konverzacemi.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

;
(function($) {
	$(document).ready(function() {
		var $contacts = $("#contacts");
		var $chatBox = $("#chat-interface");
		var isChatBoxVisible = 1;
		
		/* Nastavení viditelnosti chatu z cokie. Pokud není vyplněné, dá false. */
		if($.cookie("chat-box-visible") == 0) {
			isChatBoxVisible = 0;
			$contacts.hide();
		}
		
		var fnToogleContact = function() {
			if(isChatBoxVisible) {
				$contacts.hide();
				isChatBoxVisible = 0;
				$.cookie("chat-box-visible", 0);
			} else {
				$contacts.show();
				isChatBoxVisible = 1;
				$.cookie("chat-box-visible", 1);
			}
		};
		$("#contact-toogle-btn").click(function() {
			fnToogleContact();
		});
		/* správné nastavení velikosti chatu */
		var fnChatResize = function() {
			if($( window ).width() > 1000) {
				$contacts.show();
				$chatBox.css("height", "100%");
				$chatBox.css("bottom", "auto");
				$chatBox.css("position", "auto");
			} else {
				$chatBox.css("height", "auto");
				$chatBox.css("bottom", "0");
				$chatBox.css("position", "fixed");
			}
		};
		fnChatResize();
		/* reakce na zvětšení - zmenšení šířky okna */
		window.onresize = function() {
			fnChatResize();
		};
	});
})(jQuery);
