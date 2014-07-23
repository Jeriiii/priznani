//Url projektu
var baseUrl = window.location.href;
//Část url pro handle
var askUrl = "?do=activities-ask";
//Spojené url pro použití v ajaxu
var url = baseUrl + askUrl;

//Dotaz nanové aktivity při reloadu(tak jako na fb)
$(document).ready(function(){
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
			if('.new-counter') {
				$("#activities .new-counter").remove();
			}
			
			//Pokud jsou nové příspěvky, tak ukážeme kolik
			if(obj.count > 0) {
				$(".activities-btn").append('<div class="new-counter">' + obj.count + '</div>');
			}
		}
	});
}

// div, který překreje tlačítko při ukázání aktivit, a který pak aktivity zavře
var close='<div class="closer"></div>';
var loading = $('#loadingDiv');
var activitiesBtn = $('.activities-btn');
var activitiesDrop = $('#activities-droplink');

//Pokud se klikne mimo okno aktivit, zavře ho a odstraní zavíraci div
//Je immuni, když se kliká na zavírací element, protože ten si obstará zavření sám
$(document).mouseup(function (e)
{
	if (!activitiesDrop.is(e.target) && activitiesDrop.has(e.target).length === 0 && !$('.closer').is(e.target))
	{
		activitiesDrop.fadeOut();
		$('#activities .closer').remove();
	}
});

//Zavře okno s aktivitama a smaže zavírací div
$(document).on("click", '.closer', function(){
	activitiesDrop.fadeOut();
	$('#activities .closer').remove();
});

//Při ajaxovém načítání ukáže spinner a upraví okno aktivit
$(document).ajaxStart(function () {
	activitiesDrop.height(60).css('overflow-y', 'hidden');
	loading.show();
  })
  .ajaxStop(function () {
	activitiesDrop.height(150).css('overflow-y', 'scroll');
	loading.hide();
  });
// Při kliknutí na jednu aktivitu složí url a odešle ajax pro označení jako přečtená. 
$(document).on("click", '#activities-droplink a', function(){
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
$(document).on("click", '.marker', function(){
	var requestUrl = "?do=activities-loadActivities";
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
$(document).on("click", ".activities-btn", function(e){
	var requestUrl = "?do=activities-loadActivities";
	var ajaxUrl = baseUrl + requestUrl;
	var target = $(e.target);
	var targetAttr = target.attr('data-ajax-off');
	
	//Otevře okno s aktivitama
	activitiesDrop.fadeIn();
	$("a.activities-btn").before(close);
	$('#activities .new-counter').remove();
	
	//Obstarává poslání ajax dotazu na vykreslení aktivit, v případě kliku v intervalu 3s nenačte nové
	if(targetAttr !== '1') {
		$.nette.ajax({
			url: ajaxUrl,
			async: true,
			success: function(response) {
				activitiesBtn.attr('data-ajax-off', '1');
			},
			complete: function(payload) {
				var data = JSON.parse(payload.responseText);
				$.each(data, function(key, value) {
					$.each(value, function(key, value) {
						var div = "<div class=" + value.divClass + ">" + value.divText + "</div>";
						$(".marker").after("<a data-activity=" + value.activityID + "href=" + value.href + ">" + div + "</a>");
					});
				});
			}
		});
		setTimeout(function(){
			target.removeAttr('data-ajax-off');
		 }, 3000);

	}
});