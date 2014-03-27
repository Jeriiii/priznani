;
$(document).ready(function(){
    var container = $('#mobile-menu-container');
    var menubutton = $('#mobile-menu-button');
    menubutton.toggle(function() {
	    $( this ).addClass( "active" );
	    container.addClass("visible");
	}, function() {
	    $( this ).removeClass( "active" );
	    container.removeClass("visible");
    });
    
    $('#mobile-menu-close').click(function(){
	menubutton.trigger('click');	
    });
    
    
    
});


