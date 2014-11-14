/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

/* oteveře jiné okénko */

(function ($) {
	$(document).ready(function() {
		var fnOpenOtherWindow = function(e) {
			var $target = $(e.target);
			/* $el = tlačítko okénka, které se má spustit */
			var $el = $target.data("target");
			$($el).trigger( "click" );
		};
		$(".open-another-window").click(function(e) {
			fnOpenOtherWindow(e);
		});
	});
})(jQuery);