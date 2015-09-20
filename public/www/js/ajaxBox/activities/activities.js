/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

var fnPreventNewActivity = function () {
	$(".new-activity a").each(function () {
		$(this).click(function (e) {
			e.preventDefault();
			var $target = $(e.target);

			/* oznaceni zpravy jako prectene */
			var activityID;
			for(var i = 0;i<2;i++) {
				var activityID = $target.data("activity-viewed-id");
				if(activityID === undefined) {
					$target = $target.parent();
					activityID = $target.data("activity-viewed-id");
				}
			}

			var href = $target.attr("href");
			if(href.indexOf("?") > -1) { //obsahuje odkaz ?
				href = href + "&" + "activityViewedId=" + activityID;
			} else {
				href = href + "?" + "activityViewedId=" + activityID;
			}
			/* prejdu na odkaz v tlacitku */
			window.location = href;
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