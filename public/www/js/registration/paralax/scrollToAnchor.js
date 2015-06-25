/* 
 * Aktivuje tlačítka v paralaxu, která budou scrollovat na určitou pozici
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */
;$(document).ready(function(){
	/**
	 * 
	 * @param string selector selektor elementu, na který chci scrollovat
	 * @param string centering vertikální centování (top|middle)
	 */
	function scrollToAnchor(selector, centering){
		var element = $(selector);
		var scrollTo = element.offset().top;
		if(centering === 'middle'){/*scrollování tak, aby element byl uprostřed okna*/
			scrollTo -= ($(window).outerHeight() - element.outerHeight()) / 2;
		}else{
			scrollTo -= 53;/* to odečtené číslo je tam zatím natvrdo - je to výška headeru. Chování headeru se bude pravděpodobně ještě měnit */
		}
		$("html,body").animate({ scrollTop:scrollTo},800);
	}
	
	$('.scroll-to-btn, .next-scroll-to-btn').each(function(){
		$(this).click(function(e){
			e.preventDefault();
			var element = $(this);
			var centering = 'top';
			if(element.data("scroll-to") !== undefined){
				centering = element.data("scroll-to");
			}
			scrollToAnchor(element.attr('data-anchor'), centering);
		});
	});
	
});