/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */

$(document).ready(function(){
	$("#frm-editCityForm-city").autocomplete({
		source: data,
		minLength: 2
	});
	$.ui.autocomplete.filter = function (array, term) {
		var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(term), "i");
		return $.grep(array, function (value) {
			return matcher.test(value.label || value.value || value);
		});
	};
});