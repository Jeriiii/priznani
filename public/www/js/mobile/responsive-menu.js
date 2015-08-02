/**
 * Stará se o zobrazování a schovávání menu podle toho, kam uživatel kliká 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */
;
$(document).ready(function(){
    var container = $('#mobile-menu-container');
    var menubutton = $('#mobile-menu-button');
	/* kliknutí na tlačítko */
    menubutton.toggle(function() {
	    $( this ).addClass( "active" );
	    container.addClass("visible");
	}, function() {
	    $( this ).removeClass( "active" );
	    container.removeClass("visible");
    });
    
	/* kliknutí na zavírací tlačítko v menu */
    $('#mobile-menu-close').click(function(){
	menubutton.trigger('click');	
    });
	/* kliknutí jinam na stránku */
    $("body").click(function(a){ 
	!$(a.target).parents().is("#mobile-menu")&&container.hasClass("visible")&&menubutton.trigger('click');
    });
    
    
    
});


