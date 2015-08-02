/* 
 * Skript zajišťující správné chování aktivit na mobilní verzi. Jde o úpravu zajišťující kompatibilitu mezi aktivitami a jQuery mobile.
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */

;$(document).ready(function(){
	$("#activity-stream").on('click', '.new-activity', function (e) {
		e.preventDefault();
		var $target = $(e.target);

		/* oznaceni zpravy jako prectene */
		var activityID;
		for(var i = 0;i<2;i++) {
			var activityID = $target.data("activity-viewed-id");
			if(activityID === undefined) {
				$target = $target.parent();
				activityID = $target.data("activity-viewed-id");
			}
		}

		var href = $target.attr("href");
		if(href.indexOf("?") > -1) { //obsahuje odkaz ?
			href = href + "&" + "activityViewedId=" + activityID;
		} else {
			href = href + "?" + "activityViewedId=" + activityID;
		}
		/* prejdu na odkaz v tlacitku */
		window.location = href;
	});	
});
