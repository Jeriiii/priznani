//Url projektu
var baseUrl = window.location.href;
//Část url pro handle
var askUrl = "?do=activities-ask";
//Spojené url pro použití v ajaxu
var url = baseUrl + askUrl;

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