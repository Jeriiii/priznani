/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

var fnInitConfirm = function() {
	$('.confirm-content').each(function() {
		var $this = $(this);
		$this.ajaxBox({
			theme: "posPopUp",
			autoPosition: 'center',
			hideOthers: true
		});
		$this.find('.confirm-send-btn').click(function() {
			$('.ajaxBox').each(function() {
				$(this).css("display", "none");
			});
			$('.activeBackground').remove();
		});
	});
	
};

/* zavolá se po načtení stránky nebo ajaxovém požadavku */
$.nette.ext('complete', {
    complete: function() {
        fnInitConfirm();
    }
});