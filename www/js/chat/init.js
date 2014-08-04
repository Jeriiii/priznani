/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(function() {
	if (isLoggedIn) {//promenna pridana do renderu komponenty seznamu uzivatelu chatu
		$(document).ready(function() {
			$('body').chat();
		});
	}
});

