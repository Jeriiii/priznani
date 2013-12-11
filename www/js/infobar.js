        $(function () {
            var msie6 = $.browser == 'msie' && $.browser.version < 7;
            var rightbannerheight = 400;
            var leftposition = $("#thread").width() + 10;
			if(leftposition <= 10) //neexistuje element thread
				leftposition = $("#confession").width() + 60;
			var $rollbar = $('#rollbar');
			var $bar = $('.bar');
              if (!msie6) {
                var top = $rollbar.offset().top - parseFloat($bar.css('top').replace(/auto/, 0));
                $(window).bind('scroll', function (event) {
                  var y = $(this).scrollTop();
                  
                  if (y <= top - $rollbar.height() + $rollbar.parent().height()) {
                      $rollbar.css('top', 0);                      
                    if(y >= top){
                        $rollbar.addClass('fixed');
                        $rollbar.css('left', $rollbar.parent().position().left + leftposition);
                    } else {
                        //$('#rollbar').css('top', 0);
                        $rollbar.removeClass('fixed');
                        $rollbar.css('left', leftposition);
                    }
                  } else {
                      $rollbar.css('top', (($rollbar.parent().height()) - $bar.height()));
                      $rollbar.removeClass('fixed');
                      $rollbar.css('left', leftposition);
                  }
                });
                
              }
              
              $(document).ready(function(){
                    $rollbar.css('visibility','visible');
                    $rollbar.trigger('scroll');
                });

        });
        
        
