    //////////////PRIHLASOVACI HORNI MENU/////////////////////////
    var isvisible = false;
    var dropmenu = $('#dropmenu');
    var droplink = $('#droplink');
    droplink.click(function(){ 
        if(isvisible){
            isvisible = false;
            droplink.removeClass('pushed');//tlacitko uz neni stisknute
            dropmenu.stop().fadeTo(500, 0.0);
        }else{
            isvisible = true;
            droplink.addClass('pushed');//tlacitko je stisknute, v pushed je nastylováno jak to vypadá	    
            dropmenu.stop().fadeTo(250, 1.0);
        }
    });
    $("body").click(function(a){ 
        !$(a.target).parents().is("#usermenu")&&isvisible&&droplink.trigger('click');
    });
	
	//////////////AKTIVITY STREAM/////////////////////////
	// div, který překreje tlačítko při ukázání aktivit, a který pak aktivity zavře
	var close='<div class="closer"></div>';
	
	// Při kliknutí zobrází okno aktivit a překreje se divem pro zavření
	$('.activities-btn').click(function() {
			$('#activities-droplink').fadeIn();
			$("a.activities-btn").before(close);
	});

	//Pokud se klikne mimo okno aktivit, zavře ho a odstraní zavíraci div
	$(document).ready(function(){
		$(this).click(function() {
			$('#activities-droplink').fadeOut();
			$('#activities .closer').remove();
		});
		$('.closer').click(function() {
			$('#activities-droplink').fadeOut();
			$('#activities .closer').remove();
		});
	});
	
	//Při ajaxovém načítání ukáže spinner
	var $loading = $('#loadingDiv');
	$(document)
	  .ajaxStart(function () {
		$loading.show();
	  })
	  .ajaxStop(function () {
		$loading.hide();
	  });