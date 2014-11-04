$(document).ready(function() {
	
	//políčko s výběrem pohlaví
	var sexColumn = '#frm-advancedSearch-advancedSearchForm-sex';
	
	//políčko s výběrem pohlaví pro vybranou hodnotu
	var sexColumnSelected = '#frm-advancedSearch-advancedSearchForm-sex option:selected';
	
	//délka penisu od
	var penisLengthFrom = $('fieldset:nth-child(2)').children('.form-group').eq(1);
	
	//délka penisu do
	var penisLengthTo = $('fieldset:nth-child(2)').children('.form-group').eq(2);
	
	//šířka penisu
	var penisWidth = $('fieldset:nth-child(2)').children('.form-group').eq(3);
	
	//velikost prsou
	var breastSize = $('fieldset:nth-child(2)').children('.form-group').eq(4);
	
	//Získá vyplněnou hodnotu z políčka pohlaví
	var sex = $(sexColumnSelected).text();
	
	//defaultně je všechno schované (krom výběru pohlaví)
	penisLengthFrom.hide();
	penisLengthTo.hide();
	penisWidth.hide();
	breastSize.hide();
	
	/**
	 * Při změně hodnoty ukáže potřebná pole(w - pole s velikostí podprsenky, m - pole s délkou a šířkou penisu)
	 */
    $(sexColumn).change(function() {
        sex = $(sexColumnSelected).text();	
		
		if(sex === '--------') {
			penisLengthFrom.hide();
			penisLengthTo.hide();
			penisWidth.hide();
			breastSize.hide();
		} else if(sex === 'muž' || sex === 'pár mužů') {
			penisLengthFrom.show();
			penisLengthTo.show();
			penisWidth.show();
			breastSize.hide();
			
		} else if(sex === 'žena' || sex === 'pár žen') {
			penisLengthFrom.hide();
			penisLengthTo.hide();
			penisWidth.hide();
			breastSize.show();
		} else {
			penisLengthFrom.show();
			penisLengthTo.show();
			penisWidth.show();
			breastSize.show();
		}
	});
});