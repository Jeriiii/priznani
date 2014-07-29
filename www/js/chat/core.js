/*
 * @author Jan Kotalík
 */

;
(function($) {

	/* nastavení */
	var opts;


	/* main */
	$.fn.chat = function(options) {
		var opts = $.extend({}, $.fn.chat.defaults, options);
		setOpts(opts);
	};

	$.fn.chat.defaults = {
		minRequestTimeout: 1000,
		/* minimální čekání mezi zasíláním požadavků. Této hodnoty dosáhne chat při aktivním používání */
		maxRequestTimeout: 8000,
		/* maximální čekání mezi zasíláním požadavků na nové zprávy. Této hodnoty postupně dosáhne neaktivní uživatel */
		timeoutStep: 100
				/* o kolik se zvýší čekání při přijetí prázdné odpovědi */

	};


	function setOpts(opts) {
		this.opts = opts;
	}

})(jQuery);


