;
(function ($) {

	/* nastavení */
	var opts;
	/* odkaz na další data - pro ajax */
	var ajaxLocation;

	/* main */
	$.fn.stream = function (options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		setOpts(opts);
		setAjaxLocation(opts);
		timeCheckStream();
	};

	$.fn.stream.defaults = {
		offset: 0,
		/* kolik dalších příspěvků (dat) má plugin načíst při najetí na konec */
		addoffset: 4,
		/* html element, ze kterého se má brát odresa */
		linkElement: '#next-data-item-btn',
		/* obaluje celé tlačítko Zobrazit další */
		btnNext: '.stream-btn-next',
		/* obrázek (točící), který se zobrazí při načítání dalšího obsahu */
		streamLoader: '#stream-loader',
		/* html element obsahující zprávu pro uživatele viz. msgText */
		msgElement: '.stream-message',
		/* text zprávy, který se zobrazí když už nejsou k dispozici další data */
		msgText: "Žádné starší příspěvky nebyly nalezeny", //Žádné starší příspěvky nebyly nalezeny
		/* název parametru v URL, který nastavuje vždy aktuální offset hodnotu při každém ajaxovém požadavku */
		offsetName: 'userStream-offset'
	};

	/**
	 * Signál k zastavení dotazování - false zastaví dotazování
	 */
	$.fn.stream.run = true;

	/* prodlouží stream */
	function changeStream() {
		$(this.opts.btnNext).hide();

		/* přidá další příspěvky */
		if ($.fn.stream.run) {

			this.opts.offset = this.opts.offset + this.opts.addoffset;

			var ajaxUrl = this.ajaxLocation + "&" + this.opts.offsetName + "=" + this.opts.offset;

			$(this.opts.ajaxLocation).attr("href", ajaxUrl);

			$.nette.ajax({
				url: ajaxUrl,
				async: false,
				success: function (data, status, jqXHR) {
					if (data.snippets['snippet-userStream-posts'] == "") {//pokud snippet už neobnovuje data
						$.fn.stream.run = false;//zastaví dotazování
					}
					if (data.snippets['snippet-profilStream-posts'] == "") {//pokud snippet už neobnovuje data
						$.fn.stream.run = false;//zastaví dotazování
					}
					if (data.snippets['snippet-valChatMessages-stream-messages'] == "" || 
							data.snippets['snippet-conversation-stream-messages'] == "") {//pokud snippet už neobnovuje data
						$.fn.stream.run = false;//zastaví dotazování
					} else {
						$("#chat-stream #stream").scrollTop(30 * 50);/* posunutí chat streamu o kus níž, když se načtou data */
					}
				},
				error: function (jqXHR, status, errorThrown) {
					$.fn.stream.run = false;
				}
			});
		} else {
			/* Nejsou-li žádné další příspěvky, vypíše hlášku, že už nejsou */
			$(this.opts.msgElement).text(this.opts.msgText);
			$(this.opts.streamLoader).hide();
			$(this.opts.btnNext).hide();
		}
	}

	/* naplánuje další kontrolu za daný časový interval(půl vteřinu) */
	function timeCheckStream() {
		setTimeout(function () {
			visibleCheckStream();
		}, 500);
	}


	/* zkontroluje, zda je uživatel na konci seznamu. Když ano, zavolá prodloužení */
	function visibleCheckStream() {
		var documentScrollTop = jQuery(document).scrollTop();
		var viewportHeight = jQuery(window).height();

		var minTop = documentScrollTop;
		var maxTop = documentScrollTop + viewportHeight;
		var elementOffset = $(this.opts.streamLoader).offset();

		/* naskroluju-li nakonec stránky if větev projde */
		if (elementOffset.top >= minTop && elementOffset.top <= maxTop) {
			changeStream();
		}
		timeCheckStream();
	}

	function setOpts(opts) {
		this.opts = opts;
	}

	function setAjaxLocation(opts) {
		this.ajaxLocation = $(opts.linkElement).attr('href');
	}

})(jQuery);

