/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

/**
 * AjaxObserver neboli drbna. Registrují se u něj komponenty, za které pak pravidelně posílá na server pouze jediný požadavek
 */
;
(function ($) {

	var observerOpts;

	/* konstruktor */
	$.fn.ajaxObserver = function (options) {
		var obopts = $.extend({}, $.fn.ajaxObserver.defaults, options);
		setObserverOpts(obopts);
		refreshRequest();

		return{//vrácení funkce pro registraci, aby se jiné komponenty mohly registrovat u této instance
			register: function (key, responseFunction) {
				if (key in $.fn.ajaxObserver.regComponents) {
					console.log('Duplicate key "' + key + '" registered to AjaxObserver. Try another key.');
				} else {
					$.fn.ajaxObserver.regComponents[key] = responseFunction;
				}
			}
		};

	};

	/**
	 * Objekt obsahující registrované komponenty
	 */
	$.fn.ajaxObserver.regComponents = {};

	/**
	 * Defaultní nastavení
	 */
	$.fn.ajaxObserver.defaults = {
		/* URL, na kterou se bude komponenta dotazovat */
		requestUrl: '/',
		/* prodleva mezi dotazy na server */
		requestTimeout: 5000
	};


	/**
	 * Nastavení options
	 * @param obopts nastaveni k nastaveni
	 */
	function setObserverOpts(obopts) {
		this.observerOpts = obopts;
	}

	/* Zavolá požadavek na nová data. Poté volá sama sebe. */
	function refreshRequest() {
		var opts = this.observerOpts;

		$.getJSON(opts.requestUrl, function (jsondata) {
			$.each(jsondata, function (componentKey, data) {
				var refreshFunction = $.fn.ajaxObserver.regComponents[componentKey];
				if (refreshFunction) {
					refreshFunction(data);
				}
			});

		}).always(function () {
			setTimeout(function () {
				refreshRequest();
			}, opts.requestTimeout);
		});
	}


})(jQuery);


