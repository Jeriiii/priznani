/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

/**
 * Třída vyskakovacího ajaxového okénka. Univerzální.
 */
;
(function ($) {

	//zamek pro ajaxove pozadavky
	var ajaxLock = false;



	/* konstruktor */
	$.fn.ajaxBox = function (options) {
		var boxopts = $.extend({}, $.fn.ajaxBox.defaults, options);

		return this.each(function () {
			var $this = $(this);

			var isLoaded = $this.data("ajaxbox-is-loaded");
			if (isLoaded == undefined) { //ochrana proti spuštění pluginu dvakrát na ten samý element - děje se při ajaxu
				init($this, boxopts);
				$this.data("ajaxbox-is-loaded", true);
			}
		});

	};

	/**
	 * Nastaví pozici okénka dle nastavení
	 * @param {Object} opts parametry
	 * @param {Object} box okénko
	 * @param {Object} button tlačítko okénka
	 */
	$.fn.ajaxBox.setBoxPosition = function (opts, box, button) {
		//nastavení správné pozice
		if (opts.autoPosition) {//nastavení pozice okénka
			switch (opts.autoPosition) {
				case true:
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
					break;
				case 'center':
					box.css('top', ($(window).height() / 2) - (box.height() / 2));
					box.css('left', ($(window).width() / 2) - (box.width() / 2));
					break;
				default:
					break;
			}
		}
	};

	/*
	 * proměnné pro modul ajaxového dropdownu - zda má ajax běžet
	 */
	$.fn.ajaxBox.runStates = {};

	/*
	 * proměnné pro modul ajaxového dropdownu - aktuální offsety
	 */
	$.fn.ajaxBox.currentOffsets = {};



	/**
	 * Defaultní nastavení
	 */
	$.fn.ajaxBox.defaults = {
		/* Selektor tlačítka, které má otevírat/zavírat okno */
		buttonSelector: "",
		/* URL co se zavolá, když je potřeba načíst obsah. Prázdné nebo NULL, pokud se nemá volat vůbec */
		loadUrl: "",
		/* data, která se přibalí k požadavku o první nebo další data. Může to být i funkce.
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít) */
		dataToReload: function (opts) {
			return {};
		},
		/* funkce, co se zavolá při načtení dat
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít)
		 * @param data - data vrácená ze serveru */
		dataArrived: function (opts, data) {
		},
		/* id pro ajax Observer (pokud jej chceme použít) */
		ajaxObserverId: '',
		/* funkce zpracovávající odpověď od AjaxObserveru. Implicitně je přesune do informací u tlačítka
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít)
		 * @param data - data vrácená ze serveru */
		observerResponseHandle: function (opts, data) {
			$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
		},
		/*funkce vracející boolean, který rozhoduje o tom, zda bude ještě prováděno ajaxové volání
		 * @param opts - nastavení dotyčné komponenty (lze ji podle toho najít) */
		reloadPermitted: function (opts) {
			return true;
		},
		/* okno se otevře hned po inicializaci */
		openOnStart: false,
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
		/* při true vyvolá pozadí zakrývající vše ostatní (dle stylů) - toto pozadí je ve stylech jako .activeBackground */
		hideOthers: false,
		/* defaultní zpráva v dolní části okénka*/
		infoMessage: "",
		/* zpráva v headeru okénka */
		headerHtml: "",
		/* objekt definující zapnutí a nastavení modulu pro obnovování snippetu a kontroly jeho příchozího obsahu */
		streamSnippetModule: false,
		/* čas [ms] po kterém se okénko samo zavře, když je 0, nezavře se */
		closeAfter: 0
	};


	/**
	 * Otevře okénko
	 * @param {array} opts nastavení okénka
	 * @param {object} button objekt tlačítka, kterým byla událost vyvolána (nepovinné)
	 */
	$.fn.ajaxBox.openWindow = function (opts, button) {
		if (!button) {
			button = $(opts.buttonSelector);
		}
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';
		$(boxSelector).css('display', 'block');//otevření jediného okénka
		$(button).addClass('active');
		if (opts.hideOthers) {//vyvolani pozadi
			showBlackBackground(opts, boxSelector);
		}
		if(opts.closeAfter !== 0){/* zavření po určitém čase */
			setTimeout(function(){
				$('body').trigger('click');/* klikne se na body, aby to fungovalo i s okénky bez zavíracího křížku */
			}, opts.closeAfter);
		}
	};

	/**
	 * Vytvoří zašedlé pozadí pod okénkem
	 * @param {Object} opts nastavení
	 * @param {String} boxSelector selektor pro okénko (jedno), které má být vidět
	 */
	function showBlackBackground(opts, boxSelector) {
		$('body').prepend('<div class="activeBackground" data-related="' + opts.buttonSelector + '"></div>');
		$(boxSelector).css('z-index', '10001');
		$('.activeBackground').css('position', 'fixed');
		$('.activeBackground').css('top', 0);
		$('.activeBackground').css('left', 0);
		$('.activeBackground').css('z-index', '10000');
		$('.activeBackground').css('width', $(document).width());
		$('.activeBackground').css('height', $(document).height());
	}

	/**
	 * Nainicializuje okénko
	 * @param {Object} $this JQuery element okénka
	 * @param {Object} boxopts Nastavení
	 * @returns {undefined}
	 */
	function init($this, boxopts) {
		/* zkontroluje, zda není tlačítko předané přes data elementu */
		boxopts = getAttrData($this, boxopts);
		boxopts = applyModulesStarts(boxopts);
		//obalení okénkem a potřebnými elementy
		addHtml($this, boxopts);
		///////////////
		addBinds(boxopts);
		watchForUpdateNeed(boxopts);
		if (boxopts.ajaxObserverId) {//registrace u observeru
			observer.register(boxopts.ajaxObserverId, function (data) {
				boxopts.observerResponseHandle(boxopts, data);
			});
		}
		applyModulesEnds(boxopts);
		if (boxopts.openOnStart) {
			if (!isThisWindowVisible(boxopts)) {
				$.fn.ajaxBox.openWindow(boxopts);
			}
		}
	}

	/**
	 * Zkontroluje a nastavi nektera nastaveni. Pokud jsou prazdna, pokusi se je
	 * nastavit z atributu data.
	 * @param {object} $box Okénko co se má zobrazit.
	 * @param {object} opts Nastavení pluginu.
	 * @returns {object} Nastavení pluginu.
	 */
	function getAttrData($box, opts) {
		if (opts.buttonSelector === "" && $box.data("ajaxbox-btn") !== undefined) {
			opts.buttonSelector = $box.data("ajaxbox-btn");
		}

		if (opts.headerHtml === "" && $box.data("ajaxbox-header-html") !== undefined) {
			opts.headerHtml = $box.data("ajaxbox-header-html");
		}

		if (opts.loadUrl === "" && $box.data("ajaxbox-load-url") !== undefined) {
			opts.loadUrl = $box.data("ajaxbox-load-url");
		}

		if ($box.data("ajaxbox-additional-classes") !== undefined) {
			opts.theme = opts.theme + ' ' + $box.data("ajaxbox-additional-classes");
		}

		if ($box.data("ajaxbox-open-on-start") !== undefined) {
			opts.openOnStart = $box.data("ajaxbox-open-on-start");
		}

		if ($box.data("ajaxbox-info-message") !== undefined) {
			opts.infoMessage = $box.data("ajaxbox-info-message");
		}
		if ($box.data("ajaxbox-close-after") !== undefined) {
			opts.closeAfter = $box.data("ajaxbox-close-after");
		}
		return opts;
	}


	/** obalí data okénkem a nastaví jeho pozici vzhledem k tlačítku
	 * @param {object} data DOM objekt pro data
	 * @param {Object} opts nastavení komponenty
	 * */
	function addHtml(data, opts) {
		var button = $(opts.buttonSelector);//tlačítko
		data.appendTo('body');
		data.wrap('<div class="ajaxBox ' + opts.theme + '" data-related="' + opts.buttonSelector + '"></div>');//obalení okénkem
		var box = $('div[data-related="' + opts.buttonSelector + '"]');

		box.css('display', 'none');//okénko není vidět

		addContentWrapHtml(opts, data, box);
		addHeaderHtml(opts, box);
		addArrowHtml(opts, box);
		addInfoElementsHtml(opts, box, button);

		$.fn.ajaxBox.setBoxPosition(opts, box, button);
		data.css('display', 'block');//zobrazení dat, pokud byla skrytá
	}

	/**
	 * Obalí obsah html tagy
	 * @param {Object} opts nastavení
	 * @param {Object} data DOM object s daty
	 * @param {Object} box DOM objekt vytvořeného okénka
	 */
	function addContentWrapHtml(opts, data, box) {
		data.wrap('<div class="ajaxBoxContent"></div>');//zabalení obsahu
		data.wrap('<div class="ajaxBoxData"></div>');//zabalení obsahu
		box.find('.ajaxBoxContent').append('<span class="loadingGif clear"></span>');//gif na konci
		if (!opts.loadUrl) {
			box.find('.loadingGif').css('display', 'none');
		}
	}
	/**
	 * Přidá hlavičku okénka a zavírací křížek
	 * @param {Object} opts nastavení
	 * @param {Object} box DOM objekt vytvořeného okénka
	 */
	function addHeaderHtml(opts, box) {
		box.prepend('<div class="ajaxBoxHeader">' + opts.headerHtml + '</div>');//přidání šipečky
		if (box.hasClass('posPopUp')) {//přidání zavíracího křížku
			box.find('.ajaxBoxHeader').append('<span class="close-cross">×</span>');
		}
	}

	/**
	 * Přidá html pro šipečku okénka
	 * @param {Object} opts nastavení
	 * @param {Object} box DOM objekt vytvořeného okénka
	 */
	function addArrowHtml(opts, box) {
		box.prepend('<div class="arrow-up"></div>');//přidání šipečky
		if (opts.arrowOrientation === 'left') {//orientace okénka
			box.find('.arrow-up').addClass('on-left');//přidání třídy k šipečce (aby byla vlevo)
		}
	}
	/**
	 * Přidá daném okénku elementy s informacemi (spodní informační panel a bublinku k tlačítku)
	 * @param {Object} opts nastavení
	 * @param {Object} box DOM objekt vytvořeného okénka
	 * @param {Object} button tlačítko vytvořeného okénka
	 */
	function addInfoElementsHtml(opts, box, button) {
		box.append('<div class="window-info">' + opts.infoMessage + '</div>');//informační boxík okénka (dole)
		button.append('<div class="ajaxbox-button-info"></div>');
		button.find('.ajaxbox-button-info').css('display', 'none');
	}

	/**
	 * Znovu nebo poprvé načte data zavoláním příslušné url přes nette.ajax
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 */
	function reloadData(opts) {
		if (opts.loadUrl && opts.reloadPermitted(opts) && !this.ajaxLock) {
			this.ajaxLock = true;
			$.nette.ajax({
				url: opts.loadUrl,
				data: opts.dataToReload(opts),
				success: function (data) {
					opts.dataArrived(opts, data);
				}
			});
			this.ajaxLock = false;
		}
	}

	/**
	 * Spustí cyklus, který hlídá, zda uživatel nevidí spodní část okénka (mimo data). Pokud nevidí, pošle ajaxový požadavek
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 * */
	function watchForUpdateNeed(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"] .ajaxBoxContent';
		var option = opts;
		setInterval(function () {
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
	 * Změní nastavení, pokud je zapnutý nějaký z modulů
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 * @return upravené (nové) nastavení okénka
	 */
	function applyModulesStarts(opts) {
		if (opts.streamSnippetModule) {
			opts = applyStreamSnippetModuleStart(opts);
		}
		return opts;
	}

	/**
	 * Udělá změny zvolených modulů, které musí být aplikovány až po inicializaci
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 */
	function applyModulesEnds(opts) {
		if (opts.streamSnippetModule) {
			opts = applyStreamSnippetModuleEnd(opts);
		}
	}

	/**
	 * Pověsí na okénko (pouze toto jedno!) eventy, které ho zavřou nebo otevřou, když je potřeba
	 * @param {Object} opts nastavení daného (tohoto) okénka
	 */
	function addBinds(opts) {
		var boxSelector = 'div[data-related="' + opts.buttonSelector + '"]';
		var content = $(boxSelector).find('.ajaxBoxContent');

		bindOpenCloseListener(opts);//zavření při otevření jiného okénka
		bindCloseButtons(opts, boxSelector);
		bindCloseOnClick(opts, boxSelector);
		addSlimScroll(content);
		addResizeBehavior(opts, boxSelector);
		addReloadTrigger(opts);
	}
	
	/**
	 * Přidá možnost triggerem vyvolat obnovení dat.
	 * @param {Object} opts nastavení
	 */
	function addReloadTrigger(opts) {
		var options = opts;
		$(options.buttonSelector).bind('reloadRequest', function(){
			resetSnippets(options);
			reloadData(opts);
		});
	}
	
	/**
	 * Vyprázdní všechny snippety v datech okénka s předaným nastavením. Při použití snippet modulu také vyprádní offset.
	 * @param {Object} opts nastavení
	 */
	function resetSnippets(opts) {
		var dataElement = $('div[data-related="' + opts.buttonSelector + '"]').find('.ajaxBoxData');
		var snippets = dataElement.find('*[data-ajax-append], *[data-ajax-prepend]');
		snippets.each(function(index){
			$(this).empty();
		});
		var params = opts.streamSnippetModule;/* vyprázdnění snippetu */
		if(params){
			applyStreamSnippetModuleStart(opts);/* úplný restart modulu */
			applyStreamSnippetModuleEnd(opts);
		}
	}
	/**
	 * Nabinduje okénku click listener, který jej otevře/zavře, když se klikne na jeho tlačítko
	 * @param {Object} opts nastavení
	 */
	function bindOpenCloseListener(opts) {
		$(opts.buttonSelector).click(function (e) {
			if ($(e.target).is('.ajaxBox *, .ajaxBox')) {
				return;
			}
			var button = $(opts.buttonSelector);
			e.preventDefault();
			var close = isThisWindowVisible(opts);
			$('.ajaxBox').css('display', 'none');
			button.removeClass('active');
			$('.activeBackground').remove();
			if (!close) {
				$.fn.ajaxBox.openWindow(opts, button);
			}
		});
	}
	/**
	 * Nabinduje listener, který zavře okénko, pokud se klikne mimo něj
	 * @param {Object} opts nastavení
	 * @param {Object} boxSelector selektor okénka
	 */
	function bindCloseOnClick(opts, boxSelector) {
		$('*').click(function (event) {//zavření při kliknutí mimo okénka
			if (!$(event.target).is(opts.buttonSelector +',' + opts.buttonSelector + ' .ajaxbox-button-info', '.ajaxBox')) {
				if (!$(event.target).is('.ajaxBox *, .ajaxBox')) {
					$(boxSelector).css('display', 'none');
					$(opts.buttonSelector).removeClass('active');
					$('.activeBackground[data-related=' + opts.buttonSelector + ']').remove();
				}
			}
		});
	}

	/**
	 * Nabinduje chování okénka v případě změny velikosti okna
	 * @param {Object} opts nastavení
	 * @param {Object} boxSelector selektor okénka
	 */
	function addResizeBehavior(opts, boxSelector) {
		/* nabindování přepočítání polohy na změnu vel. okna */
		$(window).resize(function () {
			$.fn.ajaxBox.setBoxPosition(opts, $(boxSelector), $(opts.buttonSelector));
		});
	}

	/**
	 * Danému elementu přidá vlastní posuvník
	 * @param {Object} content element s obsahem
	 */
	function addSlimScroll(content) {
		//pridani vlastniho posuvniku
		content.slimScroll({
			height: content.outerHeight() + 'px'
		});
	}
	/**
	 * Nabinduje danému okénku listener, který ho za určitých podmínek zavře (např při kliknutí na zavírací tlačítko)
	 * @param {Object} opts nastavení
	 * @param {String} boxSelector selektor okénka
	 */
	function bindCloseButtons(opts, boxSelector) {
		var closeFn = function () {
			$(boxSelector).css('display', 'none');
			$(opts.buttonSelector).removeClass('active');
			$('.activeBackground[data-related=' + opts.buttonSelector + ']').remove();
		};
		$(boxSelector + ' .close-cross').click(closeFn);
		$(boxSelector + ' .close').click(closeFn);
	}



	/************MODULES**************/
	/**
	 * Nastaví funkce v nastavení tak, aby okénko fungovalo jako ajaxový dropdown
	 * @param {Object} options původní nastavení
	 * @return {Object} nové nastavení
	 */
	function applyStreamSnippetModuleStart(options) {
		var params = options.streamSnippetModule;
		$.fn.ajaxBox.runStates[params.snippetName] = true; //počáteční nastavení
		$.fn.ajaxBox.currentOffsets[params.snippetName] = params.startOffset;
		options.dataArrived = function (opts, data) {//zkoumá, jestli snippet poslal nějaká data a pokud ne, schová načítací gif a pomocí globálního přepínače zastaví dotazování
			if (data.snippets[params.snippetName].trim() == "") {
				$.fn.ajaxBox.runStates[params.snippetName] = false;
				$('div[data-related="' + opts.buttonSelector + '"] .ajaxBoxData').append('<div class="noConvMessages">' + params.endMessage + '</div>');
				$('div[data-related="' + opts.buttonSelector + '"] .loadingGif').css('display', 'none');
			}
			if (typeof params.dataArrived !== "undefined") {
				params.dataArrived(opts, data);
			}
		};
		options.reloadPermitted = function (opts) {//zastavení dotazování, pokud je globální přepínač false
			return $.fn.ajaxBox.runStates[params.snippetName];
		};
		options.dataToReload = function (opts) {//přidá ke každému požadavku ještě offset a limit. Poté zvětší offset.
			var offset = $.fn.ajaxBox.currentOffsets[params.snippetName];
			$.fn.ajaxBox.currentOffsets[params.snippetName] += params.addLimit;
			var returnObject = {};
			returnObject[params.offsetParameter] = offset;
			returnObject[params.limitParameter] = params.addLimit;
			return returnObject;
		};
		return options;
	}

	/**
	 * Finální úpravy modulu pro ajaxový dropdown
	 * @param {Object} options nastavení okénka
	 */
	function applyStreamSnippetModuleEnd(options) {
		$('div[data-related="' + options.buttonSelector + '"] .ajaxBoxData').find('.noConvMessages').remove();
		$('div[data-related="' + options.buttonSelector + '"] .loadingGif').css('display', 'block');
	}


	/*********************************/


})(jQuery);


