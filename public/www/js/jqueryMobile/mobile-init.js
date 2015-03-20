/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
$(document).bind("mobileinit", function () {/* vypnutí automatického ajaxu */
    $.mobile.ajaxEnabled = false;
});
/* zobrazení částí až po načtení kvůli problikávání*/
$(document).ready(function(){
	$('#leftmenu, #rightmenu, #topmenu').css('display', 'block');
});