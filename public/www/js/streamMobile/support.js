/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){	
	/* zobrazování a schovávání komentářů nebo upozornění, že je uživatel nepřihlášený */
	$('#stream').on('click', '.comment-button, .likes-button', function(){
		var selector = '#' + $(this).attr('data-to-open');
		$(selector).toggle();
	});
	
});

