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
		var contacts = $("#contacts");
		$("#contact-toogle-btn").click(function(e) {
			if(contacts.is(":visible")) {
				contacts.hide();
			} else {
				contacts.show();
			}
		});
		/* reakce na zvětšení - zmenšení šířky okna */
		window.onresize = function() {
			if($( window ).width() > 1000) {
				contacts.show();
			}
		};
	});
})(jQuery);
