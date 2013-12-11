$(document).ready(function(){
                var speedcompany = 3000;
		var timestep = 6000;
			
		$('.onlinebutton a img').hover(function(){
					ishover = true;
					$('.onlinebutton a img').stop();
					$(this).fadeTo(600, 0);
				}, function(){
					ishover = false;
					$('.onlinebutton a img').stop();
					$(this).fadeTo(600, 1);	
		});
		
		setTimeout(function(){
			var $button = $('.onlinebutton a img');
			$button.fadeOut(speedcompany);
			setInterval(function(){
				if($button.is(':visible')) {
					$button.fadeOut(speedcompany)
				}else{
					$button.fadeIn(speedcompany)
				}
			}, timestep);
		}, 200);
});