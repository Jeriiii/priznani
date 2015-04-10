/* 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */

;$(document).ready(function(){
	$('body').on('click', '.confirm-send-btn', function(){
		var selector = $(this).attr('data-confirm-href');
		$(selector).trigger('click');
	});
});

