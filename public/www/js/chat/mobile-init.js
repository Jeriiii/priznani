/* 
 * 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */
;$(function () {
	/* použití odkazů v seznamu kontaktů - jQueryMobile se jinak montuje do elementů <a> */
	$('#chatmenu li[data-href]').click(function(){
		window.location = $(this).attr('data-href');		
	});
});
