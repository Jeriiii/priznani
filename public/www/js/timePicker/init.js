/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
/**
 * Inicializace time pickeru
 */
 
$(document).ready(function() {
	$('input.datetimepicker').datetimepicker({
		duration: '',
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm',
		changeMonth: true,
		changeYear: true,
		yearRange: 'c:2020',
		stepMinute: 5
	});
});
