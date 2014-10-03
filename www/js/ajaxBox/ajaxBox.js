/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

/**
 * Třída vyskakovacího ajaxového okénka. Univerzální.
 */
;
(function($) {

	var boxOpts;

	/* konstruktor */
	$.fn.ajaxBox = function(options) {
		var boxopts = $.extend({}, $.fn.ajaxBox.defaults, options);
		setBoxOpts(boxopts);

		//obalení okénkem a potřebnými elementy
		addHtml($(this));
		///////////////

		addBinds();
		watchForUpdateNeed();
	};



	/**
	 * Defaultní nastavení
	 */
	$.fn.ajaxBox.defaults = {
		/* Selektor tlačítka, které má otevírat/zavírat okno */
		buttonSelector: "",
		/* URL co se zavolá, když se okénko zobrazí. Prázdné nebo NULL, pokud se nemá volat vůbec */
		loadUrl: '',
		/* URL co se zavolá, když je potřeba donačíst obsah. Prázdné nebo NULL, pokud se nemá volat vůbec. */
		refreshUrl: '',
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
		arrowOrientation: 'right'
	};


	/** obalí data okénkem a nastaví jeho pozici vzhledem k tlačítku
	 * @param {object} data DOM objekt pro data
	 * */
	function addHtml(data) {
		var opts = this.boxOpts;
		var button = $(opts.buttonSelector);//tlačítko
//		button.wrap('<span></span>');
//		button.parent().css('position', 'relative');
		data.appendTo('body');
		data.wrap('<div class="ajaxBox ' + opts.theme + '" data-related="' + opts.buttonSelector + '"></div>');//obalení okénkem
		$('.ajaxBox').css('display', 'none');//okénko není vidět
		data.wrap('<div class="ajaxBoxContent"></div>');//zabalení obsahu
		var box = data.parent().parent();//současný selektor okénka
		box.append('<span class="loadingGif clear loadIfVisible"></span>');//gif na konci
		box.append('<div class="arrow-up"></div>');//přidání šipečky
//		box.appendTo(button.parent());//přesunutí elementu do tlačítka

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
	function reloadData() {
		var opts = this.boxOpts;
		if (opts.loadUrl) {
			$.nette.ajax({
				url: opts.loadUrl
			});
		}
	}

	/**
	 * Spustí cyklus, který hlídá, zda uživatel nevidí spodní část okénka. Pokud nevidí, pošle ajaxový požadavek
	 * */
	function watchForUpdateNeed() {
		var opts = this.boxOpts;
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';
		setInterval(function() {
			var contentHeight = $(boxSelector + ' .ajaxBoxContent').height();
			var contentLeftToShow = contentHeight - $(boxSelector).scrollTop();
			if (contentLeftToShow < $(boxSelector).height()) {
				reloadData();
			}
		}, 300);
	}

	function isThisWindowVisible(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';
		return $(boxSelector).is(':visible');
	}

	/**
	 * Pověsí na okénka eventy, které je zavřou nebo otevřou, když je potřeba
	 */
	function addBinds() {
		var opts = this.boxOpts;
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
				reloadData();
			}
		});

		$('body').click(function(event) {//zavření při kliknutí mimo okénka
			if (!$(event.target).is(opts.buttonSelector, '.ajaxBox')) {
				if (!$(event.target).is('.ajaxBox *, .ajaxBox')) {
					$('.ajaxBox').css('display', 'none');
				}
			}
		});
	}






	/**
	 * Nastavení options
	 * @param opts nastaveni k nastaveni
	 */
	function setBoxOpts(opts) {
		this.boxOpts = opts;
	}


})(jQuery);


