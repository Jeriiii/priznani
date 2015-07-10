/* 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */

;$(document).ready(function(){
	
	/* skript, který u potvrzovacích dialogů jQuery UI způsobí kliknutí na skutečný odkaz
	 * Tak je možné použít nette.ajax a zároveň zavřít okno (nette.ajax totiž pochopitelně musí použít PreventDefault)*/
	$('body').on('click', '.confirm-send-btn', function(){
		var selector = $(this).attr('data-confirm-href');
		$(selector)[0].click();
	});
	
	$('#popupAddProfilePhoto').css('display', 'block');
});

