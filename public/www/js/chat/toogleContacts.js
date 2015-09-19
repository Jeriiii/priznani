/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/**
 * Zmenšuje a zvětšuje okénko s konverzacemi.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

;
(function($) {
	$(document).ready(function() {
		var $contacts = $("#contacts");
		var $chatBox = $("#chat-interface");
		var $chatToogleBtn = $("#contact-toogle-btn");
		var $chatHeader = $("#chat-header");
		var $chatSearch = $("#chat-search");
		var isChatBoxVisible = 1;
		
		/* schová chat tak, že je vidět jen jeho název - jde použít jen při malé šířce monitoru*/
		var hideChat = function() {
			$chatBox.data('height', $chatBox.height());
			$chatBox.css('height', 'auto');
			$chatBox.width($chatBox.width());
			
			$contacts.hide();
			$chatSearch.hide();
			isChatBoxVisible = 0;
			$.cookie("chat-box-visible", 0);
		};
		
		/* zvětší chat tak, že jsou vidět jeho kontakty */
		var showChat = function() {
			$chatBox.height($chatBox.data('height'));
			$contacts.show();
			$chatSearch.show();
			isChatBoxVisible = 1;
			$.cookie("chat-box-visible", 1);
		};
		
		var maximalizeChat = function (firstLoad) {
			$contacts.show();
//				$chatBox.css("height", "100%");
			//$chatBox.css("bottom", "auto");
			$chatBox.css("position", "auto");

			var pageHeight = $( window ).height();
			//$chatBox.css("height", height + "px");
			
			if(!firstLoad) {
				var chatBoxHeight =  pageHeight - $chatHeader.outerHeight();
				$chatBox.height(chatBoxHeight);
				$contacts.height(chatBoxHeight - $chatSearch.outerHeight());
				$chatBox.css("height", "auto !important");
			}

//				
//				$chatBox.height(pageHeight);
		};
		
		var minimalizeChat = function(firstLoad) {
			console.log('resize');
			$chatBox.css("height", "auto !important");
			$chatBox.css("position", "fixed");
			$contacts.css("min-width", "180px");
//			$chatBox.height($chatBox.parent.height());

			
				var pageHeight = $( window ).height();
				var correctPageHeight = pageHeight * 0.8; //velikost stránky s korekcí - bere se trochu menší, aby měl chat vůli
				if(correctPageHeight < $chatBox.height() ) { //když je chat větší než stránka

					$chatBox.height(correctPageHeight);
					if(!firstLoad) {
						var contactNewHeight = correctPageHeight - $chatToogleBtn.outerHeight() - $chatSearch.outerHeight();
						console.log($chatBox.height());
						$contacts.height(contactNewHeight);
					}

				}
			
		};
		
		/* Nastavení viditelnosti chatu z cokie. Pokud není vyplněné, dá false. */
		if($.cookie("chat-box-visible") == 0) {
			hideChat();
		}
		
		var fnToogleContact = function() {
			if(isChatBoxVisible) {
				hideChat();
			} else {
				showChat();
			}
		};
		$("#contact-toogle-btn").click(function() {
			fnToogleContact();
		});
		/* správné nastavení velikosti chatu */
		var fnChatResize = function(firstLoad) {
			if(window.innerWidth > 1150) {
				console.log("max - " + $( document ).width() + " , " + window.innerWidth);
				maximalizeChat(firstLoad);	
			} else {
				console.log("min - " + $( document ).width());
				minimalizeChat(firstLoad);
			}
		};
		fnChatResize(true);
		
		/* reakce na zvětšení - zmenšení šířky okna */
		window.onresize = function() {
			fnChatResize(false);
		};
	});
})(jQuery);
