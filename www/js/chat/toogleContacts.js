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
		var $searchIcon = $("#chat-search");
		$("#contact-toogle-btn").click(function(e) {
			if($contacts.is(":visible")) {
				$contacts.hide();
			} else {
				$contacts.show();
			}
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
