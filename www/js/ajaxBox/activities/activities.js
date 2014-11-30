/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

var fnPreventNewActivity = function () {
	$(".new-activity").each(function () {
		$(this).click(function (e) {
			e.preventDefault();
			var $target = $(e.target);
			var statusFn = function (data, status) {
			}; //musim mu predat nejakou fnci
			/* oznaceni zpravy jako prectene */
			var data;
			for(var i = 0;i<2;i++) {
				var data = $target.data("activity-viewed-link");
				if(data === undefined) {
					$target = $target.parent();
					data = $target.data("activity-viewed-link");
				}
			}
			
			$.get(data, statusFn);
			alert($target.data("activity-viewed-link"));
			alert($target.attr("href"));
			/* prejdu na odkaz v tlacitku */
			window.location = $target.attr("href");
		});
	});
};


var reloadFn = function () {
	fnPreventNewActivity();
};

/**
 * Zpracuje číselnou odpověď ajaxObserveru a nastaví ji ke tlačítku
 * @param {type} opts nastavení dotyčného okénka
 * @param {type} data příchozí data od serveru
 */
function handleNumberResponse(opts, data) {//zpracování odpovědi od ajaxObserveru, konkrétně čísla s počtem zpráv. Je-li nenulové, zobrazí se vedle tlačítka.
	if (data) {
		$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
	} else {
		$(opts.buttonSelector).find('.ajaxbox-button-info').css('display', 'none');
	}
}

$('#activities').ajaxBox({
	buttonSelector: '#activities-btn',
	topMargin: -10, //korekce y
	arrowOrientation: 'right', //šipka bude vpravo
	theme: "posAjaxBox interface activitiesBox", //použijí se implicitní styly, ale budou upraveny
	headerHtml: "Aktivity", //header
	ajaxObserverId: 'activities-observer',
	streamSnippetModule: {
		snippetName: 'snippet-activities-list',
		endMessage: 'Žádné další události.',
		offsetParameter: 'activities-offset',
		limitParameter: 'activities-limit',
		addLimit: 5,
		startOffset: 0,
		dataArrived: reloadFn
	},
	observerResponseHandle: handleNumberResponse
});