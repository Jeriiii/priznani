        ////////////////LEVE MENU//////////////////////////
        var container = $('#left-side');
        var panel = $('#left-panel');
        var label = $('#left-panel-label');
        var leftspeed = 200;
        var isappearing = false; //vysouva se - neni treba rusit stopem a posilat znova
        var isdisappearing = false;

        container.mouseenter(function(){

                if(!isappearing){
                isappearing = true;
                isdisappearing = false;
                label.stop().animate(
                        {
                        left: panel.width()
                        }
                        , leftspeed, 'linear');
                }
        });
        container.mouseleave(function(){
                if(!isdisappearing){
                isappearing = false;
                isdisappearing = true;
                label.stop().animate(
                        {
                        left: (((-1) * ($('#left-panel-label').width())) + $('#left-panel').width())
                        }
                        , leftspeed, 'linear');
                }
        });
        /////////////////hover underline////////////
        $('#left-side .first, #left-side .second, #left-side .third, #left-side .fourth').hover(function(){
                $('.' + $(this).attr('class')).css('text-decoration', 'underline');
        }, function(){
                $('.' + $(this).attr('class')).css('text-decoration', 'none');
        });