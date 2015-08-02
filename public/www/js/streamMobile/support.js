/* Podpůrné skripty k drobným funkcionalitám streamu na mobulu
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */

$(document).ready(function(){	
	/* zobrazování a schovávání komentářů nebo upozornění, že je uživatel nepřihlášený */
	$('#stream').on('click', '.comment-button, .likes-button', function(){
		var selector = '#' + $(this).attr('data-to-open');
		$(selector).toggle();
	});
	
});

