/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

var fnPreventNewActivity = function() {
	$(".new-activity").each(function(){		
		$(this).click(function(e) {
			e.preventDefault();
			var $target = $(e.target);
			var statusFn = function(data,status){}; //musim mu predat nejakou fnci
			/* oznaceni zpravy jako prectene */
			$.get($target.data("activity-viewed-link"), statusFn);
			/* prejdu na odkaz v tlacitku */
			window.location = $target.attr("href");
		});
	});
};


var reloadFn = function() {
	fnPreventNewActivity();
};
	
$('#activities').ajaxBox({
	buttonSelector: '#activities-btn',
	topMargin: -10, //korekce y
	arrowOrientation: 'right', //šipka bude vpravo
	theme: "posAjaxBox", //použijí se implicitní styly, ale budou upraveny
	headerHtml: "Aktivity", //header
	streamSnippetModule: {
		snippetName: 'snippet-activities-list',
		endMessage: 'Žádné další zprávy.',
		offsetParameter: 'activities-offset',
		limitParameter: 'activities-limit',
		addLimit: 5,
		startOffset: 0,
		dataArrived: reloadFn
	},
});