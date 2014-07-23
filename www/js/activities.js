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
				$(".activities-btn img").after('<div class="new-counter">' + obj.count + '</div>');
			}
		}
	});
}

// div, který překreje tlačítko při ukázání aktivit, a který pak aktivity zavře
var close='<div class="closer"></div>';
var loading = $('#loadingDiv');
var activitiesBtn = $('.activities-btn');
var activitiesDrop = $('#activities-droplink');

// Při kliknutí zobrází okno aktivit a překreje se divem pro zavření
activitiesBtn.click(function() {
	activitiesDrop.fadeIn();
	$('#activities .new-counter').remove();
	$("a.activities-btn").before(close);
});

//Pokud se klikne mimo okno aktivit, zavře ho a odstraní zavíraci div
$(document).mouseup(function (e)
{
	if (!activitiesDrop.is(e.target) && activitiesDrop.has(e.target).length === 0)
	{
		activitiesDrop.fadeOut();
		$('#activities .closer').remove();
	}
});

//Pokud se klikne na překrytý div zavře se okno a div se smaže(simuluje opětovný klik na šipku)
$(document).on("click",'.closer', function() {
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