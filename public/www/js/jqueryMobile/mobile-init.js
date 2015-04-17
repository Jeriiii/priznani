/* 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */
;
$(document).bind("mobileinit", function () {/* vypnutí automatického ajaxu */
    $.mobile.ajaxEnabled = false;
});
/* zobrazení částí až po načtení kvůli problikávání*/
$(document).ready(function(){
	/* zobrazení částí až po načtení kvůli problikávání*/
	$('#leftmenu, #rightmenu, #topmenu, #chatmenu').css('display', 'block');
});