/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

$(document).ready(function() {
	$("#news-more").click(function(e){
		$("#news-long").show();
		$("#news-more").show();
		$("#news-short").hide();
	});
	
	$.convRunAjax = true;
	$.convOffset = 0;
	$.convLimit = 5;

	/* stream */
	$("body").stream({
		addoffset: 4,
		snippetName: "snippet-userStream-posts"
	});
	
	/* záložky u okének na vkládání obsahu/fotek */
	$( ".stream-form" ).tabs({
		create: function(){
			/* skript, který přepne kartu, pokud obsahuje error */
			$('.stream-form.ui-tabs .ui-tabs-panel .has-error').each(function(){
				var tab = $(this).parents('.ui-tabs-panel');
				$('a.ui-tabs-anchor[href="#' + tab.attr('id') + '"]').trigger('click');/* najde příslušné tlačítko a klikne na něj*/
			});
		}
	});
	
	
	$("#frm-userStream-addConfessionForm-note").click(function() {
		$("#frm-userStream-addConfessionForm-note").animate({ height: "200px"}, 500);
	});
	$("#frm-userStream-statusForm-message").click(function() {
		$("#frm-userStream-statusForm-message").animate({ height: "200px"}, 500);
	});
});
