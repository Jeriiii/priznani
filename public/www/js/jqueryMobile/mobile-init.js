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
	/* zobrazení částí až po načtení kvůli problikávání*/
	$('#leftmenu, #rightmenu, #topmenu').css('display', 'block');
	
	$('.comment-button').click(function(){
		var selector = '#' + $(this).attr('data-comments-open');
		$(selector).toggle();
	});
});