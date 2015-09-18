;
(function ($) {

	/* nastavení */
	var opts;
	

	/* main */
	$.fn.stream = function (options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		
		return this.each(function () {
			var $this = $(this);
			setOpts($this, opts);
			init($this, opts);
		});
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
		/* selektor obrázku (točící), který se zobrazí při načítání dalšího obsahu */
		streamLoader: null, //např. streamLoader: '#stream-loader',
		/* html element obsahující zprávu pro uživatele viz. msgText */
		msgElement: '#stream-message',
		/* text zprávy, který se zobrazí když už nejsou k dispozici další data */
		msgText: "Žádné starší příspěvky nebyly nalezeny", //Žádné starší příspěvky nebyly nalezeny
		/* název parametru v URL, který nastavuje vždy aktuální offset hodnotu při každém ajaxovém požadavku */
		offsetName: 'userStream-offset',
		/* název snippetu, který zastaví dotazování, je-li prázdný */
		snippetName: '',
		/* automatické načtení dalších dat při srolování na konec stránky */
		autoload: true,
		/* funkce která se zavolá po doběhnutí ajaxového požadavku */
		fnAjaxSuccess: function(data, status){},
		/* odkaz na další data - pro ajax */
		ajaxLocation: null
	};

	/**
	 * Signál k zastavení dotazování - false zastaví dotazování
	 */
	$.fn.stream.run = true;
	
	/**
	 * Zavede a spustí plugin.
	 * @param {Object} $this Tato instance pluginu (je při spuštění pluginu vícekrát na jedné stránce)
	 * @param {Object} opts
	 */
	function init($this, opts) {
		$this.ajaxLocation = getAjaxLocation(opts);

		initNextBtn(opts);

		/* rozhodne, zda se má používat automatické donačítání dat */
		if(opts.autoload) {
			timeCheckStream(opts);
		} else {
			if(opts.streamLoader !== null) { 
				$(opts.streamLoader).hide();
			}
		}
	}

	/* prodlouží stream */
	function changeStream(opts) {
		var $btnNext = $(opts.btnNext);
		$btnNext.hide();

		/* přidá další příspěvky */
		if ($.fn.stream.run) {
			setOffset(opts);
			var ajaxUrl = getAjaxUrl(opts, opts.ajaxLocation);
			console.log(opts);
			console.log(ajaxUrl);
			ajax(ajaxUrl, opts);
			
			$btnNext.show();
		} else {
			/* Nejsou-li žádné další příspěvky, vypíše hlášku, že už nejsou */
			hideBtn(opts);
		}
	}
	
	/**
	 * Schová tlačítko a všechno co s tím souvisí.
	 * @param {Object} opts
	 */
	function hideBtn(opts) {
		$(opts.msgElement).text(opts.msgText);
		$(opts.streamLoader).hide();
		$(opts.btnNext).remove();
	}
	
	/**
	 * Funkce která se spustí po úspěšném provedení AJAX požadavku.
	 * @param {Object} opts
	 * @returns {Function}
	 */
	function fnAjaxSuccess(opts) {
		return function(data, status, jqXHR) {
			var snippetName = opts.snippetName;
			if (snippetName != '' && data.snippets[snippetName] == "") {//pokud snippet už neobnovuje data
				$.fn.stream.run = false;//zastaví dotazování
				hideBtn(opts);
				
			}
			opts.fnAjaxSuccess(data, status);
		};
	}

	/** 
	 * Naplánuje další kontrolu za daný časový interval(půl vteřinu)
	 * @param {Object} opts
	 */
	function timeCheckStream(opts) {
		if(opts.autoload) {
			setTimeout(function () {
				visibleCheckStream(opts);
			}, 500);
		}
	}


	/** 
	 * Zkontroluje, zda je uživatel na konci seznamu. Když ano, zavolá prodloužení 
	 * @param {Object} opts
	 */
	function visibleCheckStream(opts) {
		var documentScrollTop = $(document).scrollTop();
		var viewportHeight = $(window).height();

		var minTop = documentScrollTop;
		var maxTop = documentScrollTop + viewportHeight;
		var elementOffset = $(opts.streamLoader).offset();

		/* naskroluju-li nakonec stránky if větev projde */
		if (elementOffset.top >= minTop && elementOffset.top <= maxTop) {
			changeStream(opts);
		}
		timeCheckStream(opts);
	}

	function setOpts($this, opts) {
		$this.opts = opts;
		opts.this = $this;
	}

	/**
	 * Vrátí základní odkaz (bez offsetu a limitu) na načtení dalších dat do streamu.
	 * @param {Object} opts
	 * @return {string} Základní odkaz (bez offsetu a limitu) na načtení dalších dat do streamu.
	 */
	function getAjaxLocation(opts) {
		return opts.ajaxLocation = $(opts.linkElement).attr('href');
	}
	
	/**
	 * Načte další položky do streamu.
	 * @param {string} ajaxUrl Url, která se zavolá pro načtení dalších položek. 
	 * Má limit i offset a vrací výsledky.
	 * @param {Object} opts
	 */
	function ajax(ajaxUrl, opts) {		
		$.nette.ajax({
			url: ajaxUrl,
			async: false,
			success: fnAjaxSuccess(opts),
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
		return ajaxLocation + "&" + opts.offsetName + "=" + opts.offset;
	}	
	
	/**
	 * Nastaví nový offset.
	 * @param {Object} opts
	 */	
	function setOffset(opts) {
		opts.offset = opts.offset + opts.addoffset;
	}
	
	/**
	 * 
	 * @param {type} opts
	 * @returns {undefined}
	 */
	function initNextBtn(opts) {
		$(opts.linkElement).click(function(e) {
			e.preventDefault();
			changeStream(opts);
		});
	}

})(jQuery);

