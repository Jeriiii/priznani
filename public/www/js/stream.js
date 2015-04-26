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
		this.ajaxLocation = getAjaxLocation(opts);
		
		initNextBtn(opts, this.ajaxLocation);
		
		/* rozhodne, zda se má používat automatické donačítání dat */
		if(opts.autoLoadData) {
			timeCheckStream();
		} else {
			$(opts.streamLoader).hide();
		}
	};

	$.fn.stream.defaults = {
		/* aktuální offset */
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
		offsetName: 'userStream-offset',
		/* název snippetu, který zastaví dotazování, je-li prázdný */
		snippetName: '',
		/* automatické načtení dalších dat při srolování na konec stránky */
		autoLoadData: true
	};

	/**
	 * Signál k zastavení dotazování - false zastaví dotazování
	 */
	$.fn.stream.run = true;

	/* prodlouží stream */
	function changeStream() {
		var $btnNext = $(this.opts.btnNext);
		$btnNext.hide();

		/* přidá další příspěvky */
		if ($.fn.stream.run) {
			var ajaxUrl = getAjaxUrl(this.opts, this.ajaxLocation);
			setNextBtn(this.opts, ajaxUrl);
			
			ajax(ajaxUrl, this.opts);
			
			$btnNext.show();
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

	/**
	 * Vrátí základní odkaz (bez offsetu a limitu) na načtení dalších dat do streamu.
	 * @param {Object} opts
	 * @return {string} Základní odkaz (bez offsetu a limitu) na načtení dalších dat do streamu.
	 */
	function getAjaxLocation(opts) {
		return this.ajaxLocation = $(opts.linkElement).attr('href');
	}
	
	/**
	 * Načte další položky do streamu.
	 * @param {string} ajaxUrl Url, která se zavolá pro načtení dalších položek. 
	 * Má limit i offset a vrací výsledky.
	 * @param {Object} opts
	 */
	function ajax(ajaxUrl, opts) {
		var snippetName = opts.snippetName;
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
				if (data.snippets[snippetName] == "") {//pokud snippet už neobnovuje data
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
	}
	
	/**
	 * Vrátí url k načtení dalších dat do streamu.
	 * @param {Object} opts
	 * @param {string} ajaxLocation Url k načtení dalších dat do streamu bez offsetu a limitu.
	 * @returns {String} Url k načtení dalších dat do streamu
	 */
	function getAjaxUrl(opts, ajaxLocation) {
		opts.offset = opts.offset + opts.addoffset;
		return ajaxLocation + "&" + opts.offsetName + "=" + opts.offset;
	}
	
	/**
	 * Nastaví správné url u tlačítka na poslání dalších dat do streamu.
	 * @param {Object} opts
	 * @param {string} ajaxUrl Url k načtení dalších dat do streamu
	 */
	function setNextBtn(opts, ajaxUrl) {
		$(opts.linkElement).attr("href", ajaxUrl);
	}
	
	/**
	 * 
	 * @param {type} opts
	 * @param {type} ajaxLocation
	 * @returns {undefined}
	 */
	function initNextBtn(opts, ajaxLocation) {
		var ajaxUrl = getAjaxUrl(opts, ajaxLocation);
		setNextBtn(opts, ajaxUrl);
		
		$(opts.linkElement).click(function(e) {
			e.preventDefault();
			changeStream();
		});
	}

})(jQuery);

