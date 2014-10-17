$(document).ready(function() {
	//Získá vyplněnou hodnotu z políčka pohlaví
	var sex = $('#frm-advancedSearch-advancedSearchForm-sex option:selected').text();
	
	$('fieldset:nth-child(2)').children('.form-group').eq(1).hide();
	$('fieldset:nth-child(2)').children('.form-group').eq(2).hide();
	$('fieldset:nth-child(2)').children('.form-group').eq(3).hide();
	$('fieldset:nth-child(2)').children('.form-group').eq(4).hide();
	
	/**
	 * Při změně hodnoty ukáže potřebná pole(w - pole s velikostí podprsenky, m - pole s délkou a šířkou penisu)
	 */
    $('#frm-advancedSearch-advancedSearchForm-sex').change(function() {
        var sex = $('#frm-advancedSearch-advancedSearchForm-sex option:selected').text();	
		
		if(sex === '---') {
			$('fieldset:nth-child(2)').children('.form-group').eq(1).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(2).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(3).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(4).hide();
		} else if(sex === 'muž' || sex === 'pár mužů') {
			$('fieldset:nth-child(2)').children('.form-group').eq(1).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(2).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(3).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(4).hide();
			
		} else if(sex === 'žena' || sex === 'pár žen') {
			$('fieldset:nth-child(2)').children('.form-group').eq(1).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(2).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(3).hide();
			$('fieldset:nth-child(2)').children('.form-group').eq(4).show();
		} else {
			$('fieldset:nth-child(2)').children('.form-group').eq(1).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(2).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(3).show();
			$('fieldset:nth-child(2)').children('.form-group').eq(4).show();
		}
	});
});