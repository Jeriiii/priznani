/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */
$(function() {
	if (isLoggedIn) {//promenna pridana do renderu komponenty seznamu uzivatelu chatu - chat se nacte jen kdyz je uzivatel prihlaseny
		$(document).ready(function() {
			$('body').chat();
		});
	}
});

