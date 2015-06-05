/* 
 * 
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  * 
 */
;$(function(){
	$(document).ready(function(){
		$('.chat-search-icon').click(function(){
			$('#chat-search-input').trigger('focus');
		});
		var searchInput = $('#chat-search-input');
		searchInput.on('input', function(){
			var inputText = searchInput.val().toLowerCase();/* text v inputu */
			$('#contacts li').each(function(){
				var contactInList = $(this);
				var comparedText = $(this).text().toLowerCase(); /* porovnávaný řetězec kontaktu */
				if(comparedText.search(inputText) >= 0){/* obsahuje napsaný řetězec? */
					contactInList.css('display', 'block');
				}else{
					contactInList.css('display', 'none');
				}
			});
		});		
	});	
});

