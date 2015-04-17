/* 
 * 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */

/* Funguje jako stream, ale donačítání se spouští tlačítkem */
;$(document).ready(function(){
	
	var currentOffset = 0;
	var chatAjaxLock = false;
	
	$('#next-data-item-btn').click(function(e){
		e.preventDefault();
		if(!chatAjaxLock){
			chatAjaxLock = true;
			var button = $(this);
			var ajaxUrl = $(this).attr('href');
			var loaderGif = $('#stream-loader');
			loaderGif.css('display', 'block');
			ajaxUrl = ajaxUrl + "&" + 'conversation-offset' + "=" + currentOffset;//získáni url
			
			$.nette.ajax({//ajaxový požadavek
				url: ajaxUrl,
				async: false,
				success: function (data, status, jqXHR) {
					loaderGif.css('display', 'none');
					chatAjaxLock = false;
					currentOffset += 30;
					
					if (data.snippets['snippet-conversation-stream-messages'] == "") {//pokud snippet už neobnovuje data
						button.css('display', 'none');
						var message = $('#stream .stream-info-message');
						message.html('Žádné předchozí zprávy nebyly nalezeny.');
						message.css('display', 'block');
					}
				},
				error: function (jqXHR, status, errorThrown) {
					chatAjaxLock = false;
				}
			});
		}
	
	});
});
/* přenačtení stylů, když jde nette ajax*/
$.nette.ext('chatStreamAjax', {
		success: function () {/* úspěch operace z nette.ajax */
			$('#chat-stream form').trigger("create");/* znovuaplikování jQueryMobile na stream */
		}
});