;(function($) {
	/**
	 * Proměnná pro nastavení
	 * @type object
	 */
	var settings;
	
	/**
	 * zavedení scriptu jako pluginu
	 * @param {object} options
	 */
	$.fn.activities = function(options) {
		var settings = $.extend({}, $.fn.activities.defaults, options);
		setSettings(settings);
		//Dotaz nanové aktivity při reloadu(tak jako na fb)
		getNewActivitiesCount();
		closeActivitiesOnOutClick(settings.activitiesBtn, settings.activitiesDrop);
		//Volání funkce na počet nových aktivit každých 30s
		setInterval(getNewActivitiesCount, 30000);
		
		//hlavní obluha aktivit
		activitiesMain(settings.activitiesBtn, settings.activitiesDrop, settings.baseUrl, settings.requestUrl, settings.loading);
		//obsluha označení aktivity jako přečtené
		activityRead(settings.baseUrl, settings.viewedUrl);
	};
	/**
	 * Defaultní hodnoty funkce
	 */
	$.fn.activities.defaults = {
		//Url aktuálního okna
		baseUrl: window.location.origin + window.location.pathname,
		//Část url pro handle
		askUrl: "?do=activities-ask&" + (window.location.search).substr(1),
		// div, který překreje tlačítko při ukázání aktivit, a který pak aktivity zavře
		loading: '#loadingDiv',
		// div zvoneček
		activitiesBtn: '.activities-btn',
		// div s aktivitami
		activitiesDrop: '#activities-droplink',
		//část url s handlerem pro load aktivit
		requestUrl: "?do=activities-loadActivities&" + (window.location.search).substr(1),
		//Část url volání handleru na označení přečtené aktivity
		viewedUrl: "do=activities-viewed&" + (window.location.search).substr(1)
	};
	

	/**
	 * Vyvolá revalidaci komponenty po označení všech odpovědí jako přečtených
	 */
	$('.marker').click(function() {
		//složená url pro načtení aktivit
		var wholeUrl = this.settings.baseUrl + this.settings.requestUrl;
		$.nette.ajax({
			url: wholeUrl,
			async: true,
			success: function(response) {

			},
			complete: function(payload) {

			}
		});
	});

	/**
	 * Funkce volá ajax, a povolí načtení každé 3s při kliku
	 * @param {string} activitiesBtn
	 * @param {string} activitiesDrop
	 * @param {string} baseUrl
	 * @param {string} requestUrl
	 * @param {string} loading
	 */
	function activitiesMain(activitiesBtn, activitiesDrop, baseUrl, requestUrl, loading) {
		$(activitiesBtn).click(function(e) {
			//celá url pro ajax na load aktivit
			var ajaxUrl = baseUrl + requestUrl;
			//klinutý element
			var target = $(e.target);
			//atribut, který se přidá po kliknutí
			var targetAttr = target.attr('data-ajax-off');

			//pokud se má okno s aktivitami otevřít, ukáže spinner, pokud se má zavřít, neukáže ho
			manageLoadingDiv(activitiesDrop, loading);

			//Otevře nebo zavře okno s aktivitama
			$(activitiesDrop).fadeToggle();

			//Obstarává poslání ajax dotazu na vykreslení aktivit, v případě kliku v intervalu 3s nenačte nové
			manageActivities(ajaxUrl, targetAttr, activitiesBtn, loading);

			//zavoláme funkci, která po intervalu odmaže vypnuti ajaxu
			ajaxOffTimer(activitiesBtn);
		});
	}
	
	/**
	 * Označí aktivitu jako přečtenou
	 * @param {string} baseUrl
	 * @param {string} viewedUrl
	 */
	function activityRead(baseUrl, viewedUrl) {
		$(document).on("click", '#activities-droplink button', function() {
		//Získání id aktivity
		var activityID = $(this).attr('data-activity');
		//Složená url
		var wholeUrl = baseUrl + "?activities-activityID=" + activityID + "&" + viewedUrl;
		$.nette.ajax({
			url: wholeUrl,
			async: true,
			success: function(response) {

			},
			complete: function(payload) {

			}
		});
	});
	}
	
	/**
	 * Pokud jsou otevřené aktivity a klikne se mimo, zavřou se
	 * @param {string} activitiesBtn
	 * @param {string} activitiesDrop
	 * @returns {undefined}
	 */
	function closeActivitiesOnOutClick(activitiesBtn, activitiesDrop) {
		$('body').click(function(){
		if( !$(event.target).is(activitiesBtn, activitiesDrop) ){
			if( !$(event.target).is(activitiesDrop)) {
				if($(activitiesDrop).is(':visible')) {
					$(activitiesDrop).fadeOut();
				}
			}
		}
	});
	}
	
	/**
	 * Ajax dotaz na nové aktivity a vypsání počtu
	 */
	function getNewActivitiesCount() {
		var url = this.settings.baseUrl + this.settings.askUrl;
		
		//odeslání ajaxu
		$.nette.ajax({
			url: url,
			dataType: "json",
			async: true,
			success: function(response) {
	
			},
			complete: function(payload) {
				showNewActivitiesCount(payload);
			}
		});
	}
	
	/**
	 * Pokud jsou nové aktivity, ukáže kolik
	 * @param {JSON} payload
	 */
	function showNewActivitiesCount(payload) {
		//Získání části JSONu s daty
		var json = (payload.responseText);
		//Rozparsování
		var obj = JSON.parse(json);
		//Pokud už bylo počítadlo vykresleno, smažeme
		if ('.new-counter') {
			$("#activities .new-counter").remove();
		}

		//Pokud jsou nové příspěvky, tak ukážeme kolik
		if (obj.count > 0) {
			$(this.settings.activitiesBtn).append('<div class="new-counter">' + obj.count + '</div>');
		}
	}
	
	/**
	 * Funkce obsluhuje vykreslování aktivit
	 * @param {string} ajaxUrl
	 * @param {string} targetAttr
	 * @param {string} activitiesBtn
	 * @param {string} loading
	 */
	function manageActivities(ajaxUrl, targetAttr, activitiesBtn, loading) {
		if (targetAttr !== '1') {
			$('#activities .new-counter').remove();
			$.nette.ajax({
				url: ajaxUrl,
				async: true,
				success: function(response) {
					$(loading).hide();
				},
				complete: function(payload) {
					$(activitiesBtn).attr('data-ajax-off', '1');
					var data = JSON.parse(payload.responseText);

					//Pokud uživatel nemá žádnou událost, vypíše se mu to.
					if (data.activities === 0) {
						$('#activities .marker').hide();

						//Kontrola, zda div s oznámením již nebyl jednou vykreslen a jeho případné ukázání
						showNoActivitiesSign();
					} else {
						//Kontrola, zda div s oznámením již nebyl jednou vykreslen a jeho případné schování
						hideNoActivitiesSign();
						
						//Zobrazení markeru na označení všech zpráv jako přečtené
						$('#activities .marker').show();
						
						
						//Promazání příspěvků, aby se stále neopakovaly
						deleteActivities();
						
						//Promazání buttonů u aktivit, zajišťujících funkcionalitu ohledně označení přečtených
						deleteActivitiesButtons();
				
						//Vypsání každé události
						printActivities(data);
					}
				}
			});
		}
	}
	
	/**
	 * Obluhuje loading div v souvislosti s oknem aktivit
	 * @param {string} activitiesDrop
	 * @param {string} loading
	 */
	function manageLoadingDiv(activitiesDrop, loading) {
		if(!$(activitiesDrop).is(':visible')) {
			$(loading).show();
		} else {
			$(loading).hide();
		}
	}
	
	/**
	 * Zkontroluje přítomnost oznámení a případně ho ukáže
	 */
	function showNoActivitiesSign() {
		if ($('.no-activities').length === 0) {
			$(this.settings.loading).after('<div class="no-activities">Zatím nemáte žádné události</div>');
		}
	}
	
	/**
	 * Zkontroluje přítomnost oznámení a případně ho schová
	 */
	function hideNoActivitiesSign() {
		if ($('.no-activities').length !== 0) {
			$('.no-activities').hide();
		}
	}
	
	/**
	 * Promaže buttony označující přečtené aktivity
	 */
	function deleteActivitiesButtons(){
		$('#activities-droplink button').each(function() {
			$(this).remove();
		});
	}
	
	/**
	 * Při zavolání promaže okno s aktivitami
	 */
	function deleteActivities() {
		$('#activities-droplink .item').each(function() {
			$(this).remove();
		});
	}
	
	/**
	 * Zajišťuje vykreslení všech aktivit
	 * @param {JSON} data
	 */
	function printActivities(data) {
		$.each(data, function(key, value) {
			$.each(value, function(key, value) {
				if(value.viewed === 0) {
					var div = "<div class=" + value.divClass + ">" + value.divText + "</div>";
					$(".marker").after("<a class=item data-activity=" + value.activityID + " href=" + value.href + ">" + div + "</a><button data-activity='" + value.activityID + "' >Přečtené</button>");
				} else {
					var div = "<div class=" + value.divClass + ">" + value.divText + "</div>";
					$(".marker").after("<a class=item data-activity=" + value.activityID + " href=" + value.href + ">" + div + "</a>");
				}
			});
		});
	}
	
	/**
	 * Po třech sekundách odstraní informaci o vypnutém ajaxu
	 * @param {string} activitiesBtn
	 */
	function ajaxOffTimer(activitiesBtn) {
		setTimeout(function() {
			$(activitiesBtn).removeAttr('data-ajax-off');
		}, 3000);
	}
	
	/**
	 * Setter na settings
	 * @param {Object} settings
	 * @returns {_L1.setSettings}
	 */
	function setSettings(settings) {
		this.settings = settings;
	}

})(jQuery);