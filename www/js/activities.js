;(function($) {
	//Url projektu
	var baseUrl = window.location.origin + window.location.pathname;
	//Část url pro handle
	var askUrl = "?do=activities-ask";
	//složená url pro dotaz na nové aktivity
	var url = baseUrl + askUrl;

	//Dotaz nanové aktivity při reloadu(tak jako na fb)
	$(document).ready(function() {
		getNewActivitiesCount();
	});


	//Volání funkce každých 30s
	setInterval(getNewActivitiesCount, 30000);

	//Ajax dotaz na nové aktivity
	function getNewActivitiesCount() {
		$.nette.ajax({
			url: url,
			dataType: "json",
			async: true,
			success: function(response) {

			},
			complete: function(payload) {
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
					$(".activities-btn").append('<div class="new-counter">' + obj.count + '</div>');
				}
			}
		});
	}

	// div, který překreje tlačítko při ukázání aktivit, a který pak aktivity zavře
	var loading = $('#loadingDiv');
	// div zvoneček
	var activitiesBtn = $('.activities-btn');
	// div s aktivitami
	var activitiesDrop = $('#activities-droplink');

	//pokud se klikne mimo buttonek na zavírání nebo seznam aktivit, zavře se okno aktivit,
	//pokud je otevřené
	$('html').click(function(){
    if( !$(event.target).is(activitiesBtn) ){
		if( !$(event.target).is(activitiesDrop)) {
			if(activitiesDrop.is(':visible')) {
				$(activitiesDrop).fadeOut();
			}
		}
    }
});

	// Při kliknutí na jednu aktivitu složí url a odešle ajax pro označení jako přečtená.
	$(document).on("click", '#activities-droplink button', function() {
		//Získání id aktivity
		var activityID = $(this).attr('data-activity');
		//Část url volání handleru
		var viewedUrl = "do=activities-viewed";
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

	//Vyvolá revalidaci komponenty po označení všech odpovědí jako přečtených
	$('.marker').click(function() {
		//část url s handlerem pro load aktivit
		var requestUrl = "?do=activities-loadActivities";
		//složená url pro načtení aktivit
		var wholeUrl = baseUrl + requestUrl;
		$.nette.ajax({
			url: wholeUrl,
			async: true,
			success: function(response) {

			},
			complete: function(payload) {

			}
		});
	});

	//funkce volá ajax, a povolí načtení každé 3s při kliku
	$(activitiesBtn).click(function(e) {
		//část url s handlerem pro load aktivit
		var requestUrl = "?do=activities-loadActivities";
		//celá url pro ajax na load aktivit
		var ajaxUrl = baseUrl + requestUrl;
		//klinutý element
		var target = $(e.target);
		//atribut, který se přidá po kliknutí
		var targetAttr = target.attr('data-ajax-off');

		//pokud se má okno s aktivitami otevřít, ukáže spinner, pokud se má zavřít, neukáže ho
		if(!activitiesDrop.is(':visible')) {
			loading.show();
		} else {
			loading.hide();
		}

		//Otevře nebo zavře okno s aktivitama
		activitiesDrop.fadeToggle();
		//Obstarává poslání ajax dotazu na vykreslení aktivit, v případě kliku v intervalu 3s nenačte nové
		if (targetAttr !== '1') {
			$('#activities .new-counter').remove();
			$.nette.ajax({
				url: ajaxUrl,
				async: true,
				success: function(response) {
				},
				complete: function(payload) {
					activitiesBtn.attr('data-ajax-off', '1');
					loading.hide();
					var data = JSON.parse(payload.responseText);

					//Pokud uživatel nemá žádnou událost, vypíše se mu to.
					if (data.activities === 0) {
						$('#activities .marker').remove();

						//Kontrola, zda div s oznámením již nebyl jednou vykreslen
						if ($('.no-activities').length === 0) {
							$('#loadingDiv').after('<div class="no-activities">Zatím nemáte žádné události.</div>');
						}
					} else {
						//Promazání příspěvků, aby se stále neopakovaly
						$('#activities-droplink .item').each(function() {
							$(this).remove();
						});
						//Vypsání každé události
						$.each(data, function(key, value) {
							$.each(value, function(key, value) {
								var div = "<div class=" + value.divClass + ">" + value.divText + "</div>";
								$(".marker").after("<a class=item data-activity=" + value.activityID + " href=" + value.href + ">" + div + "</a><button data-activity='" + value.activityID + "' >Přečtené</button>");
							});
						});
					}
				}
			});
		}
		//Nastaví time na 3s, kvůli zamezení posílání ajaxu kadým klikem v rychlém sledu
			setTimeout(function() {
				target.removeAttr('data-ajax-off');
			}, 3000);
	});

})(jQuery);