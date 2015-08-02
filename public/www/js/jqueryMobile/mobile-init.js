/* 
 * Stará se o správnou inicializaci skriptů k jQuery mobile
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */
;
/* vypnutí automatického ajaxu (jQuery Mobile má funkci, že samo volá ajaxové požadavky, ale nehodilo se to)*/
$(document).bind("mobileinit", function () {
    $.mobile.ajaxEnabled = false;
});
/* zobrazení částí až po načtení kvůli problikávání - ve stylech jsou všechny prvky, které by problikávaly (např. obsah javascriptových okének) schované, teprve až po načtení se zobrazí*/
$(document).ready(function(){
	/* zobrazení částí až po načtení kvůli problikávání*/
	$('#leftmenu, #rightmenu, #topmenu, #chatmenu, #popupAddProfilePhoto, #stream, #popupAddProfilePhoto').css('display', 'block');
});