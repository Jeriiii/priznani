/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/* ojednoduch0 vzskakovací okénko */
$(document).ready(function() {
	$('.ajaxbox-popUp').each(function() {
		$(this).ajaxBox({
			theme: "posPopUp",
			autoPosition: 'center',
			hideOthers: true
		});
	});
});