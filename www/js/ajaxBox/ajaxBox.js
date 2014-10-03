/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

/**
 * Třída vyskakovacího ajaxového okénka. Univerzální.
 */
;
(function($) {

	//zamek pro ajaxove pozadavky
	var ajaxLock = false;

	/* konstruktor */
	$.fn.ajaxBox = function(options) {
		var boxopts = $.extend({}, $.fn.ajaxBox.defaults, options);


		return this.each(function() {
			//obalení okénkem a potřebnými elementy
			addHtml($(this), boxopts);
			///////////////

			addBinds(boxopts);
			watchForUpdateNeed(boxopts);
			if (boxopts.ajaxObserverId) {
				observer.register(boxopts.ajaxObserverId, function(data) {
					boxopts.observerResponseHandle(boxopts, data);
				});
			}
		});

	};



	/**
	 * Defaultní nastavení
	 */
	$.fn.ajaxBox.defaults = {
		/* Selektor tlačítka, které má otevírat/zavírat okno */
		buttonSelector: "",
		/* URL co se zavolá, když je potřeba načíst obsah. Prázdné nebo NULL, pokud se nemá volat vůbec */
		loadUrl: '',
		/* data, která se přibalí k požadavku o první nebo další data. Může to být i funkce.
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít) */
		dataToReload: function(opts) {
			return {};
		},
		/* funkce, co se zavolá při načtení dat
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít)
		 * @param data - data vrácená ze serveru */
		dataArrived: function(opts, data) {
		},
		/* id pro ajax Observer (pokud jej chceme použít) */
		ajaxObserverId: '',
		/* funkce zpracovávající odpověď od AjaxObserveru. Implicitně je přesune do informací u tlačítka
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít)
		 * @param data - data vrácená ze serveru */
		observerResponseHandle: function(opts, data) {
			$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
		},
		/*funkce vracející boolean, který rozhoduje o tom, zda bude ještě prováděno ajaxové volání
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít) */
		reloadPermitted: function(opts) {
			return true;
		},
		/* CSS třída přiřazená rodičovskému elementu - zde lze nastavit rozměry okna apod */
		theme: 'posAjaxBox',
		/* automatické nastavení pozice okénka pod tlačítko (false pokud si ji chcete nastavit ve stylech) Při zapnutí bude pozice nastavena podle šipky */
		autoPosition: true,
		/* odsazení okénka od tlačítka po ose y (pokud je zapnuté autoPosition). Může být i záporné. Jen číselná hodnota bez px. */
		topMargin: 0,
		/* korekce pozice okénka po ose x (pokud je zapnuté autoPosition). Může být i záporné. Jen číselná hodnota bez px. */
		leftMargin: 0,
		/* orientace šipky - left|right - šipka je vlevo|vpravo. Podle toho se nastaví i pozice okénka. Předpokládá se, že šipka má nastavené
		 * css left|right (pozor na to pokud děláte vlastní theme!!!). */
		arrowOrientation: 'right',
		/* defaultní zpráva v dolní části okénka*/
		defaultMessage: ''
	};


	/** obalí data okénkem a nastaví jeho pozici vzhledem k tlačítku
	 * @param {object} data DOM objekt pro data
	 * */
	function addHtml(data, opts) {
		var button = $(opts.buttonSelector);//tlačítko
		data.appendTo('body');
		data.wrap('<div class="ajaxBox ' + opts.theme + '" data-related="' + opts.buttonSelector + '"></div>');//obalení okénkem
		$('.ajaxBox').css('display', 'none');//okénko není vidět
		data.wrap('<div class="ajaxBoxContent"></div>');//zabalení obsahu
		data.wrap('<div class="ajaxBoxData"></div>');//zabalení obsahu
		var box = data.parent().parent().parent();//současný selektor okénka
		box.find('.ajaxBoxContent').append('<span class="loadingGif clear loadIfVisible"></span>');//gif na konci
		box.prepend('<div class="arrow-up"></div>');//přidání šipečky
		box.append('<div class="window-info">' + opts.defaultMessage + '</div>');//informační boxík okénka (dole)
		button.append('<div class="ajaxbox-button-info"></div>');
		button.find('.ajaxbox-button-info').css('display', 'none');

		if (opts.arrowOrientation === 'left') {//orientace okénka
			box.find('.arrow-up').addClass('on-left');//přidání třídy k šipečce (aby byla vlevo)
		}
		//nastavení správné pozice
		if (opts.autoPosition) {//nastavení pozice okénka
			var arrow = box.find('.arrow-up');
			box.css('top', button.outerHeight() + arrow.outerHeight() + opts.topMargin);//nastavení xové souřadnice
			if (opts.arrowOrientation === 'left') {//rozdělení podle orientace
				var arrowCorrection = parseInt(arrow.css('left')) + arrow.outerWidth() / 2;//vzdálenost zleva ke středu šipky
				box.css('left', button.offset().left + (button.outerWidth() / 2) - arrowCorrection + opts.leftMargin);//nastavení odsazení zleva
			} else {
				var arrowCorrection = parseInt(arrow.css('right')) + arrow.outerWidth() / 2;//vzdálenost zprava ke středu šipky
				var offsetRight = $(window).width() - button.offset().left - button.outerWidth();//offset tlačítka zprava
				box.css('right', offsetRight + (button.outerWidth() / 2) - arrowCorrection - opts.leftMargin);//nastavení odsazení zprava
			}

		}
		data.css('display', 'block');//zobrazení dat, pokud byla skrytá
	}

	/**
	 * Znovu nebo poprvé načte data zavoláním příslušné url přes nette.ajax
	 */
	function reloadData(opts) {
		if (opts.loadUrl && opts.reloadPermitted(opts) && !this.ajaxLock) {
			this.ajaxLock = true;
			$.nette.ajax({
				url: opts.loadUrl,
				data: opts.dataToReload(opts),
				success: function(data) {
					opts.dataArrived(opts, data);
				}
			});
			this.ajaxLock = false;
		}
	}

	/**
	 * Spustí cyklus, který hlídá, zda uživatel nevidí spodní část okénka (mimo data). Pokud nevidí, pošle ajaxový požadavek
	 * */
	function watchForUpdateNeed(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"] .ajaxBoxContent';
		var option = opts;
		setInterval(function() {
			if (isThisWindowVisible(opts)) {
				var contentHeight = $(boxSelector + ' .ajaxBoxData').height();
				var contentLeftToShow = contentHeight - $(boxSelector).scrollTop();
				if (contentLeftToShow < $(boxSelector).height()) {
					reloadData(option);
				}
			}
		}, 1000);
	}

	/** vrátí true|false, je-li dané okénko viditelné*
	 *
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 * @returns {boolean}
	 */
	function isThisWindowVisible(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';
		return $(boxSelector).is(':visible');
	}

	/**
	 * Pověsí na okénka eventy, které je zavřou nebo otevřou, když je potřeba
	 */
	function addBinds(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';

		$(opts.buttonSelector).click(function(e) {//zavření při otevření jiného okénka
			if ($(e.target).is('.ajaxBox *, .ajaxBox')) {
				return;
			}
			e.preventDefault();
			var close = isThisWindowVisible(opts);
			$('.ajaxBox').css('display', 'none');
			if (!close) {
				$(boxSelector).css('display', 'block');//otevření jediného okénka
			}
		});

		$('body').click(function(event) {//zavření při kliknutí mimo okénka
			if (!$(event.target).is(opts.buttonSelector, '.ajaxBox')) {
				if (!$(event.target).is('.ajaxBox *, .ajaxBox')) {
					$(boxSelector).css('display', 'none');
				}
			}
		});
	}


})(jQuery);


