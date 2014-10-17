;/* 
 *  @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */
 
/**
 * Obstarává autocomplete pro formulář pro přidání povolených lidí
 * pro galerii
 *
 * @author Daniel Holubář
 */
$(document).ready(function(){
	prepareAutocomplete();
});

/**
 * Obstarává opětovné navázání autocomplete po přidání uživatele
 * @param function
 */
$(document).ajaxComplete(function (event, xhr, settings){
	//Kontrola, aby se regovalo jen na specifický ajaxový požadavek
	if(settings.url.indexOf("allowUserForm-submit") > 0 || settings.url.indexOf("do=removeFriend") > 0){
		prepareAutocomplete();
	};
});

/**
 * Metoda provede ajaxový požadavek pro získání dat a pošle je pro další zpracování
 */
function prepareAutocomplete() {
	var baseUrl = window.location.origin + window.location.pathname;
	var galleryParam = processParams(document.URL);
	var askUrl = "?do=myUserImagesInGallery-getUsersForSuggest";
	if(galleryParam !== false) {
		var url = baseUrl + askUrl + galleryParam;
	} else {
		var url = baseUrl + askUrl;
	}

	$.nette.ajax({
		url: url,
		async: true,
		success: function(response) {
		},
		complete: function(payload) {
			processResult(JSON.parse(payload.responseText));
		}
	});
}

/**
 * Metoda zpracuje data z ajaxu a připojí autocoplete k formuláři
 * @param Object data
 */
function processResult(data) {
	var dataArray = [];
	$.each(data, function (key, value) {
				dataArray.push(value.user);
			});
			 $("#frm-allowUserForm-user_name").autocomplete({
				source: dataArray
			});
			$.ui.autocomplete.filter = function (array, term) {
				var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(term), "i");
				return $.grep(array, function (value) {
					return matcher.test(value.label || value.value || value);
				});
			};
}

/**
 * Zpracuje parametry v url
 * @param string url adresa
 * @returns {String|Boolean} pokud jsou parametry vrátí je jako string pokud ne vrátí false
 */
function processParams(url) {
	var startIndex = url.indexOf("?") + 1;
	if(startIndex > 0) {
		var substring = "&" + url.substring(startIndex);
		return substring;
	}
	return false;
}