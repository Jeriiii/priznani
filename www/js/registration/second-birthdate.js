/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/**
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

;
(function($) {
	fnSetVisitableBirstdate = function() {
		var secondBirthdate = $("#second-birdhdate");
		$("select[name=type]").change(function() {
			var typeVal = $(this).attr("value");
			
			var labelBDFirst = $("#label-datebirth-first");
			var labelBDSecond = $("#label-datebirth-second");
			
			var manVal = 1;
			var womanVal = 2;
			var couple = 3;
			var coupleMen = 4;
			var coupleWonem = 5;
			var groupVal = 6;
			/* schování - zobrazení druhého věku narození */
			if(typeVal == manVal || typeVal == womanVal || typeVal == groupVal) {
				secondBirthdate.hide();
			} else {
				secondBirthdate.show();
				/* změna labelů věku narození */
				if(typeVal == couple) {
					labelBDFirst.text("Datum narození Partnerky");
					labelBDSecond.text("Datum narození Partnera");
				}
				if(typeVal == coupleMen) {
					labelBDFirst.text("Datum narození");
					labelBDSecond.text("Datum narození Partnera");
				}
				if(typeVal == coupleWonem) {
					labelBDFirst.text("Datum narození");
					labelBDSecond.text("Datum narození Partnerky");
				}
			}
			
			
		});
	};
	$(document).ready(function(){
		fnSetVisitableBirstdate();
	});
})(jQuery);
