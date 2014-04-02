    //////////////PRIHLASOVACI HORNI MENU/////////////////////////
    var isvisible = false;
    var dropmenu = $('#dropmenu');
    var droplink = $('#droplink');
    //dropmenu.css('display', 'none');
    //dropmenu.css('visibility', 'visible');
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