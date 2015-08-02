/* 
 * Inicializace ajax observeru pro mobilní verzi - callbacky jsou totiž jiné než v desktopové verzi
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */

;$(document).ready(function(){
	/**
	 * Změní informační poznámku u daného objektu tak, že mu přidá/odebere příslušnou třídu a html s daty
	 * @param {type} linkObject
	 * @param {type} data
	 * @returns {undefined}
	 */
	var changeInfoData = function(linkObject, data){
		if(data == 0){
			linkObject.html('');
			linkObject.removeClass('with-info');
			return;
		}
		if(!linkObject.hasClass('with-info')){
			linkObject.addClass('with-info');
			linkObject.html('<span class="info"></span>');
		}
		linkObject.find('span.info').html(data);
	};
	/* inicializace observeru pro mobily (hlášení nových aktivit a zpráv) */
	observer.register('chatConversationWindow', function(data){
		changeInfoData($('#chat-button'), data);
	});
	observer.register('activities-observer', function(data){
		changeInfoData($('#activities-button'), data);
	});
});
